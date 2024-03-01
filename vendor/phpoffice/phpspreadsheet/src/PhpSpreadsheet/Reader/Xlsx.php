<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\DefinedName;
use PhpOffice\PhpSpreadsheet\Reader\Security\XmlScanner;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\AutoFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Chart;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\ColumnAndRowAttributes;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\ConditionalStyles;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\DataValidations;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Hyperlinks;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\PageSetup;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Properties as PropertyReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\SheetViewOptions;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\SheetViews;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Styles;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Theme;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\WorkbookView;
use PhpOffice\PhpSpreadsheet\ReferenceHelper;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Shared\Drawing;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Shared\Font;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font as StyleFont;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;
use Throwable;
use XMLReader;
use ZipArchive;

class Xlsx extends BaseReader
{
    const INITIAL_FILE = '_rels/.rels';

    /**
     * ReferenceHelper instance.
     *
     * @var ReferenceHelper
     */
    private $referenceHelper;

    /**
     * @var ZipArchive
     */
    private $zip;

    /** @var Styles */
    private $styleReader;

    /**
     * Create a new Xlsx Reader instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->referenceHelper = ReferenceHelper::getInstance();
        $this->securityScanner = XmlScanner::getInstance($this);
    }

    /**
     * Can the current IReader read the file?
     */
    public function canRead(string $filename): bool
    {
        if (!File::testFileNoThrow($filename, self::INITIAL_FILE)) {
            return false;
        }

        $result = false;
        $this->zip = $zip = new ZipArchive();

        if ($zip->open($filename) === true) {
            [$workbookBasename] = $this->getWorkbookBaseName();
            $result = !empty($workbookBasename);

            $zip->close();
        }

        return $result;
    }

    /**
     * @param mixed $value
     */
    public static function testSimpleXml($value): SimpleXMLElement
    {
        return ($value instanceof SimpleXMLElement) ? $value : new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
    }

    public static function getAttributes(?SimpleXMLElement $value, string $ns = ''): SimpleXMLElement
    {
        return self::testSimpleXml($value === null ? $value : $value->attributes($ns));
    }

    // Phpstan thinks, correctly, that xpath can return false.
    // Scrutinizer thinks it can't.
    // Sigh.
    private static function xpathNoFalse(SimpleXmlElement $sxml, string $path): array
    {
        return self::falseToArray($sxml->xpath($path));
    }

    /**
     * @param mixed $value
     */
    public static function falseToArray($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function loadZip(string $filename, string $ns = ''): SimpleXMLElement
    {
        $contents = $this->getFromZipArchive($this->zip, $filename);
        $rels = simplexml_load_string(
            $this->securityScanner->scan($contents),
            'SimpleXMLElement',
            Settings::getLibXmlLoaderOptions(),
            $ns
        );

        return self::testSimpleXml($rels);
    }

    // This function is just to identify cases where I'm not sure
    // why empty namespace is required.
    private function loadZipNonamespace(string $filename, string $ns): SimpleXMLElement
    {
        $contents = $this->getFromZipArchive($this->zip, $filename);
        $rels = simplexml_load_string(
            $this->securityScanner->scan($contents),
            'SimpleXMLElement',
            Settings::getLibXmlLoaderOptions(),
            ($ns === '' ? $ns : '')
        );

        return self::testSimpleXml($rels);
    }

    private const REL_TO_MAIN = [
        Namespaces::PURL_OFFICE_DOCUMENT => Namespaces::PURL_MAIN,
        Namespaces::THUMBNAIL => '',
    ];

    private const REL_TO_DRAWING = [
        Namespaces::PURL_RELATIONSHIPS => Namespaces::PURL_DRAWING,
    ];

    private const REL_TO_CHART = [
        Namespaces::PURL_RELATIONSHIPS => Namespaces::PURL_CHART,
    ];

    /**
     * Reads names of the worksheets from a file, without parsing the whole file to a Spreadsheet object.
     *
     * @param string $filename
     *
     * @return array
     */
    public function listWorksheetNames($filename)
    {
        File::assertFile($filename, self::INITIAL_FILE);

        $worksheetNames = [];

        $this->zip = $zip = new ZipArchive();
        $zip->open($filename);

        //    The files we're looking at here are small enough that simpleXML is more efficient than XMLReader
        $rels = $this->loadZip(self::INITIAL_FILE, Namespaces::RELATIONSHIPS);
        foreach ($rels->Relationship as $relx) {
            $rel = self::getAttributes($relx);
            $relType = (string) $rel['Type'];
            $mainNS = self::REL_TO_MAIN[$relType] ?? Namespaces::MAIN;
            if ($mainNS !== '') {
                $xmlWorkbook = $this->loadZip((string) $rel['Target'], $mainNS);

                if ($xmlWorkbook->sheets) {
                    foreach ($xmlWorkbook->sheets->sheet as $eleSheet) {
                        // Check if sheet should be skipped
                        $worksheetNames[] = (string) self::getAttributes($eleSheet)['name'];
                    }
                }
            }
        }

        $zip->close();

        return $worksheetNames;
    }

    /**
     * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns).
     *
     * @param string $filename
     *
     * @return array
     */
    public function listWorksheetInfo($filename)
    {
        File::assertFile($filename, self::INITIAL_FILE);

        $worksheetInfo = [];

        $this->zip = $zip = new ZipArchive();
        $zip->open($filename);

        $rels = $this->loadZip(self::INITIAL_FILE, Namespaces::RELATIONSHIPS);
        foreach ($rels->Relationship as $relx) {
            $rel = self::getAttributes($relx);
            $relType = (string) $rel['Type'];
            $mainNS = self::REL_TO_MAIN[$relType] ?? Namespaces::MAIN;
            if ($mainNS !== '') {
                $relTarget = (string) $rel['Target'];
                $dir = dirname($relTarget);
                $namespace = dirname($relType);
                $relsWorkbook = $this->loadZip("$dir/_rels/" . basename($relTarget) . '.rels', '');

                $worksheets = [];
                foreach ($relsWorkbook->Relationship as $elex) {
                    $ele = self::getAttributes($elex);
                    if (
                        ((string) $ele['Type'] === "$namespace/worksheet") ||
                        ((string) $ele['Type'] === "$namespace/chartsheet")
                    ) {
                        $worksheets[(string) $ele['Id']] = $ele['Target'];
                    }
                }

                $xmlWorkbook = $this->loadZip($relTarget, $mainNS);
                if ($xmlWorkbook->sheets) {
                    $dir = dirname($relTarget);
                    /** @var SimpleXMLElement $eleSheet */
                    foreach ($xmlWorkbook->sheets->sheet as $eleSheet) {
                        $tmpInfo = [
                            'worksheetName' => (string) self::getAttributes($eleSheet)['name'],
                            'lastColumnLetter' => 'A',
                            'lastColumnIndex' => 0,
                            'totalRows' => 0,
                            'totalColumns' => 0,
                        ];

                        $fileWorksheet = (string) $worksheets[(string) self::getArrayItem(self::getAttributes($eleSheet, $namespace), 'id')];
                        $fileWorksheetPath = strpos($fileWorksheet, '/') === 0 ? substr($fileWorksheet, 1) : "$dir/$fileWorksheet";

                        $xml = new XMLReader();
                        $xml->xml(
                            $this->securityScanner->scanFile(
                                'zip://' . File::realpath($filename) . '#' . $fileWorksheetPath
                            ),
                            null,
                            Settings::getLibXmlLoaderOptions()
                        );
                        $xml->setParserProperty(2, true);

                        $currCells = 0;
                        while ($xml->read()) {
                            if ($xml->localName == 'row' && $xml->nodeType == XMLReader::ELEMENT && $xml->namespaceURI === $mainNS) {
                                $row = $xml->getAttribute('r');
                                $tmpInfo['totalRows'] = $row;
                                $tmpInfo['totalColumns'] = max($tmpInfo['totalColumns'], $currCells);
                                $currCells = 0;
                            } elseif ($xml->localName == 'c' && $xml->nodeType == XMLReader::ELEMENT && $xml->namespaceURI === $mainNS) {
                                $cell = $xml->getAttribute('r');
                                $currCells = $cell ? max($currCells, Coordinate::indexesFromString($cell)[0]) : ($currCells + 1);
                            }
                        }
                        $tmpInfo['totalColumns'] = max($tmpInfo['totalColumns'], $currCells);
                        $xml->close();

                        $tmpInfo['lastColumnIndex'] = $tmpInfo['totalColumns'] - 1;
                        $tmpInfo['lastColumnLetter'] = Coordinate::stringFromColumnIndex($tmpInfo['lastColumnIndex'] + 1);

                        $worksheetInfo[] = $tmpInfo;
                    }
                }
            }
        }

        $zip->close();

        return $worksheetInfo;
    }

    private static function castToBoolean(SimpleXMLElement $c): bool
    {
        $value = isset($c->v) ? (string) $c->v : null;
        if ($value == '0') {
            return false;
        } elseif ($value == '1') {
            return true;
        }

        return (bool) $c->v;
    }

    private static function castToError(?SimpleXMLElement $c): ?string
    {
        return isset($c, $c->v) ? (string) $c->v : null;
    }

    private static function castToString(?SimpleXMLElement $c): ?string
    {
        return isset($c, $c->v) ? (string) $c->v : null;
    }

    /**
     * @param mixed $value
     * @param mixed $calculatedValue
     */
    private function castToFormula(?SimpleXMLElement $c, string $r, string &$cellDataType, &$value, &$calculatedValue, array &$sharedFormulas, string $castBaseType): void
    {
        if ($c === null) {
            return;
        }
        $attr = $c->f->attributes();
        $cellDataType = 'f';
        $value = "={$c->f}";
        $calculatedValue = self::$castBaseType($c);

        // Shared formula?
        if (isset($attr['t']) && strtolower((string) $attr['t']) == 'shared') {
            $instance = (string) $attr['si'];

            if (!isset($sharedFormulas[(string) $attr['si']])) {
                $sharedFormulas[$instance] = ['master' => $r, 'formula' => $value];
            } else {
                $master = Coordinate::indexesFromString($sharedFormulas[$instance]['master']);
                $current = Coordinate::indexesFromString($r);

                $difference = [0, 0];
                $difference[0] = $current[0] - $master[0];
                $difference[1] = $current[1] - $master[1];

                $value = $this->referenceHelper->updateFormulaReferences($sharedFormulas[$instance]['formula'], 'A1', $difference[0], $difference[1]);
            }
        }
    }

    /**
     * @param string $fileName
     */
    private function fileExistsInArchive(ZipArchive $archive, $fileName = ''): bool
    {
        // Root-relative paths
        if (strpos($fileName, '//') !== false) {
            $fileName = substr($fileName, strpos($fileName, '//') + 1);
        }
        $fileName = File::realpath($fileName);

        // Sadly, some 3rd party xlsx generators don't use consistent case for filenaming
        //    so we need to load case-insensitively from the zip file

        // Apache POI fixes
        $contents = $archive->locateName($fileName, ZipArchive::FL_NOCASE);
        if ($contents === false) {
            $contents = $archive->locateName(substr($fileName, 1), ZipArchive::FL_NOCASE);
        }

        return $contents !== false;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getFromZipArchive(ZipArchive $archive, $fileName = '')
    {
        // Root-relative paths
        if (strpos($fileName, '//') !== false) {
            $fileName = substr($fileName, strpos($fileName, '//') + 1);
        }
        // Relative paths generated by dirname($filename) when $filename
        // has no path (i.e.files in root of the zip archive)
        $fileName = (string) preg_replace('/^\.\//', '', $fileName);
        $fileName = File::realpath($fileName);

        // Sadly, some 3rd party xlsx generators don't use consistent case for filenaming
        //    so we need to load case-insensitively from the zip file

        // Apache POI fixes
        $contents = $archive->getFromName($fileName, 0, ZipArchive::FL_NOCASE);
        if ($contents === false) {
            $contents = $archive->getFromName(substr($fileName, 1), 0, ZipArchive::FL_NOCASE);
        }

        return ($contents === false) ? '' : $contents;
    }

    /**
     * Loads Spreadsheet from file.
     */
    protected function loadSpreadsheetFromFile(string $filename): Spreadsheet
    {
        File::assertFile($filename, self::INITIAL_FILE);

        // Initialisations
        $excel = new Spreadsheet();
        $excel->removeSheetByIndex(0);
        $addingFirstCellStyleXf = true;
        $addingFirstCellXf = true;

        $unparsedLoadedData = [];

        $this->zip = $zip = new ZipArchive();
        $zip->open($filename);

        //    Read the theme first, because we need the colour scheme when reading the styles
        [$workbookBasename, $xmlNamespaceBase] = $this->getWorkbookBaseName();
        $drawingNS = self::REL_TO_DRAWING[$xmlNamespaceBase] ?? Namespaces::DRAWINGML;
        $chartNS = self::REL_TO_CHART[$xmlNamespaceBase] ?? Namespaces::CHART;
        $wbRels = $this->loadZip("xl/_rels/{$workbookBasename}.rels", Namespaces::RELATIONSHIPS);
        $theme = null;
        $this->styleReader = new Styles();
        foreach ($wbRels->Relationship as $relx) {
            $rel = self::getAttributes($relx);
            $relTarget = (string) $rel['Target'];
            if (substr($relTarget, 0, 4) === '/xl/') {
                $relTarget = substr($relTarget, 4);
            }
            switch ($rel['Type']) {
                case "$xmlNamespaceBase/theme":
                    $themeOrderArray = ['lt1', 'dk1', 'lt2', 'dk2'];
                    $themeOrderAdditional = count($themeOrderArray);

                    $xmlTheme = $this->loadZip("xl/{$relTarget}", $drawingNS);
                    $xmlThemeName = self::getAttributes($xmlTheme);
                    $xmlTheme = $xmlTheme->children($drawingNS);
                    $themeName = (string) $xmlThemeName['name'];

                    $colourScheme = self::getAttributes($xmlTheme->themeElements->clrScheme);
                    $colourSchemeName = (string) $colourScheme['name'];
                    $colourScheme = $xmlTheme->themeElements->clrScheme->children($drawingNS);

                    $themeColours = [];
                    foreach ($colourScheme as $k => $xmlColour) {
                        $themePos = array_search($k, $themeOrderArray);
                        if ($themePos === false) {
                            $themePos = $themeOrderAdditional++;
                        }
                        if (isset($xmlColour->sysClr)) {
                            $xmlColourData = self::getAttributes($xmlColour->sysClr);
                            $themeColours[$themePos] = (string) $xmlColourData['lastClr'];
                        } elseif (isset($xmlColour->srgbClr)) {
                            $xmlColourData = self::getAttributes($xmlColour->srgbClr);
                            $themeColours[$themePos] = (string) $xmlColourData['val'];
                        }
                    }
                    $theme = new Theme($themeName, $colourSchemeName, $themeColours);
                    $this->styleReader->setTheme($theme);

                    break;
            }
        }

        $rels = $this->loadZip(self::INITIAL_FILE, Namespaces::RELATIONSHIPS);

        $propertyReader = new PropertyReader($this->securityScanner, $excel->getProperties());
        foreach ($rels->Relationship as $relx) {
            $rel = self::getAttributes($relx);
            $relTarget = (string) $rel['Target'];
            $relType = (string) $rel['Type'];
            $mainNS = self::REL_TO_MAIN[$relType] ?? Namespaces::MAIN;
            switch ($relType) {
                case Namespaces::CORE_PROPERTIES:
                    $propertyReader->readCoreProperties($this->getFromZipArchive($zip, $relTarget));

                    break;
                case "$xmlNamespaceBase/extended-properties":
                    $propertyReader->readExtendedProperties($this->getFromZipArchive($zip, $relTarget));

                    break;
                case "$xmlNamespaceBase/custom-properties":
                    $propertyReader->readCustomProperties($this->getFromZipArchive($zip, $relTarget));

                    break;
                    //Ribbon
                case Namespaces::EXTENSIBILITY:
                    $customUI = $relTarget;
                    if ($customUI) {
                        $this->readRibbon($excel, $customUI, $zip);
                    }

                    break;
                case "$xmlNamespaceBase/officeDocument":
                    $dir = dirname($relTarget);

                    // Do not specify namespace in next stmt - do it in Xpath
                    $relsWorkbook = $this->loadZip("$dir/_rels/" . basename($relTarget) . '.rels', '');
                    $relsWorkbook->registerXPathNamespace('rel', Namespaces::RELATIONSHIPS);

                    $sharedStrings = [];
                    $relType = "rel:Relationship[@Type='"
                        //. Namespaces::SHARED_STRINGS
                        . "$xmlNamespaceBase/sharedStrings"
                        . "']";
                    $xpath = self::getArrayItem($relsWorkbook->xpath($relType));

                    if ($xpath) {
                        $xmlStrings = $this->loadZip("$dir/$xpath[Target]", $mainNS);
                        if (isset($xmlStrings->si)) {
                            foreach ($xmlStrings->si as $val) {
                                if (isset($val->t)) {
                                    $sharedStrings[] = StringHelper::controlCharacterOOXML2PHP((string) $val->t);
                                } elseif (isset($val->r)) {
                                    $sharedStrings[] = $this->parseRichText($val);
                                }
                            }
                        }
                    }

                    $worksheets = [];
                    $macros = $customUI = null;
                    foreach ($relsWorkbook->Relationship as $elex) {
                        $ele = self::getAttributes($elex);
                        switch ($ele['Type']) {
                            case Namespaces::WORKSHEET:
                            case Namespaces::PURL_WORKSHEET:
                                $worksheets[(string) $ele['Id']] = $ele['Target'];

                                break;
                            case Namespaces::CHARTSHEET:
                                if ($this->includeCharts === true) {
                                    $worksheets[(string) $ele['Id']] = $ele['Target'];
                                }

                                break;
                                // a vbaProject ? (: some macros)
                            case Namespaces::VBA:
                                $macros = $ele['Target'];

                                break;
                        }
                    }

                    if ($macros !== null) {
                        $macrosCode = $this->getFromZipArchive($zip, 'xl/vbaProject.bin'); //vbaProject.bin always in 'xl' dir and always named vbaProject.bin
                        if ($macrosCode !== false) {
                            $excel->setMacrosCode($macrosCode);
                            $excel->setHasMacros(true);
                            //short-circuit : not reading vbaProject.bin.rel to get Signature =>allways vbaProjectSignature.bin in 'xl' dir
                            $Certificate = $this->getFromZipArchive($zip, 'xl/vbaProjectSignature.bin');
                            if ($Certificate !== false) {
                                $excel->setMacrosCertificate($Certificate);
                            }
                        }
                    }

                    $relType = "rel:Relationship[@Type='"
                        . "$xmlNamespaceBase/styles"
                        . "']";
                    $xpath = self::getArrayItem(self::xpathNoFalse($relsWorkbook, $relType));

                    if ($xpath === null) {
                        $xmlStyles = self::testSimpleXml(null);
                    } else {
                        $xmlStyles = $this->loadZip("$dir/$xpath[Target]", $mainNS);
                    }

                    $palette = self::extractPalette($xmlStyles);
                    $this->styleReader->setWorkbookPalette($palette);
                    $fills = self::extractStyles($xmlStyles, 'fills', 'fill');
                    $fonts = self::extractStyles($xmlStyles, 'fonts', 'font');
                    $borders = self::extractStyles($xmlStyles, 'borders', 'border');
                    $xfTags = self::extractStyles($xmlStyles, 'cellXfs', 'xf');
                    $cellXfTags = self::extractStyles($xmlStyles, 'cellStyleXfs', 'xf');

                    $styles = [];
                    $cellStyles = [];
                    $numFmts = null;
                    if (/*$xmlStyles && */ $xmlStyles->numFmts[0]) {
                        $numFmts = $xmlStyles->numFmts[0];
                    }
                    if (isset($numFmts) && ($numFmts !== null)) {
                        $numFmts->registerXPathNamespace('sml', $mainNS);
                    }
                    $this->styleReader->setNamespace($mainNS);
                    if (!$this->readDataOnly/* && $xmlStyles*/) {
                        foreach ($xfTags as $xfTag) {
                            $xf = self::getAttributes($xfTag);
                            $numFmt = null;

                            if ($xf['numFmtId']) {
                                if (isset($numFmts)) {
                                    $tmpNumFmt = self::getArrayItem($numFmts->xpath("sml:numFmt[@numFmtId=$xf[numFmtId]]"));

                                    if (isset($tmpNumFmt['formatCode'])) {
                                        $numFmt = (string) $tmpNumFmt['formatCode'];
                                    }
                                }

                                // We shouldn't override any of the built-in MS Excel values (values below id 164)
                                //  But there's a lot of naughty homebrew xlsx writers that do use "reserved" id values that aren't actually used
                                //  So we make allowance for them rather than lose formatting masks
                                if (
                                    $numFmt === null &&
                                    (int) $xf['numFmtId'] < 164 &&
                                    NumberFormat::builtInFormatCode((int) $xf['numFmtId']) !== ''
                                ) {
                                    $numFmt = NumberFormat::builtInFormatCode((int) $xf['numFmtId']);
                                }
                            }
                            $quotePrefix = (bool) ($xf['quotePrefix'] ?? false);

                            $style = (object) [
                                'numFmt' => $numFmt ?? NumberFormat::FORMAT_GENERAL,
                                'font' => $fonts[(int) ($xf['fontId'])],
                                'fill' => $fills[(int) ($xf['fillId'])],
                                'border' => $borders[(int) ($xf['borderId'])],
                                'alignment' => $xfTag->alignment,
                                'protection' => $xfTag->protection,
                                'quotePrefix' => $quotePrefix,
                            ];
                            $styles[] = $style;

                            // add style to cellXf collection
                            $objStyle = new Style();
                            $this->styleReader->readStyle($objStyle, $style);
                            if ($addingFirstCellXf) {
                                $excel->removeCellXfByIndex(0); // remove the default style
                                $addingFirstCellXf = false;
                            }
                            $excel->addCellXf($objStyle);
                        }

                        foreach ($cellXfTags as $xfTag) {
                            $xf = self::getAttributes($xfTag);
                            $numFmt = NumberFormat::FORMAT_GENERAL;
                            if ($numFmts && $xf['numFmtId']) {
                                $tmpNumFmt = self::getArrayItem($numFmts->xpath("sml:numFmt[@numFmtId=$xf[numFmtId]]"));
                                if (isset($tmpNumFmt['formatCode'])) {
                                    $numFmt = (string) $tmpNumFmt['formatCode'];
                                } elseif ((int) $xf['numFmtId'] < 165) {
                                    $numFmt = NumberFormat::builtInFormatCode((int) $xf['numFmtId']);
                                }
                            }

                            $quotePrefix = (bool) ($xf['quotePrefix'] ?? false);

                            $cellStyle = (object) [
                                'numFmt' => $numFmt,
                                'font' => $fonts[(int) ($xf['fontId'])],
                                'fill' => $fills[((int) $xf['fillId'])],
                                'border' => $borders[(int) ($xf['borderId'])],
                                'alignment' => $xfTag->alignment,
                                'protection' => $xfTag->protection,
                                'quotePrefix' => $quotePrefix,
                            ];
                            $cellStyles[] = $cellStyle;

                            // add style to cellStyleXf collection
                            $objStyle = new Style();
                            $this->styleReader->readStyle($objStyle, $cellStyle);
                            if ($addingFirstCellStyleXf) {
                                $excel->removeCellStyleXfByIndex(0); // remove the default style
                                $addingFirstCellStyleXf = false;
                            }
                            $excel->addCellStyleXf($objStyle);
                        }
                    }
                    $this->styleReader->setStyleXml($xmlStyles);
                    $this->styleReader->setNamespace($mainNS);
                    $this->styleReader->setStyleBaseData($theme, $styles, $cellStyles);
                    $dxfs = $this->styleReader->dxfs($this->readDataOnly);
                    $styles = $this->styleReader->styles();

                    $xmlWorkbook = $this->loadZipNoNamespace($relTarget, $mainNS);
                    $xmlWorkbookNS = $this->loadZip($relTarget, $mainNS);

                    // Set base date
                    if ($xmlWorkbookNS->workbookPr) {
                        Date::setExcelCalendar(Date::CALENDAR_WINDOWS_1900);
                        $attrs1904 = self::getAttributes($xmlWorkbookNS->workbookPr);
                        if (isset($attrs1904['date1904'])) {
                            if (self::boolean((string) $attrs1904['date1904'])) {
                                Date::setExcelCalendar(Date::CALENDAR_MAC_1904);
                            }
                        }
                    }

                    // Set protection
                    $this->readProtection($excel, $xmlWorkbook);

                    $sheetId = 0; // keep track of new sheet id in final workbook
                    $oldSheetId = -1; // keep track of old sheet id in final workbook
                    $countSkippedSheets = 0; // keep track of number of skipped sheets
                    $mapSheetId = []; // mapping of sheet ids from old to new

                    $charts = $chartDetails = [];

                    if ($xmlWorkbookNS->sheets) {
                        /** @var SimpleXMLElement $eleSheet */
                        foreach ($xmlWorkbookNS->sheets->sheet as $eleSheet) {
                            $eleSheetAttr = self::getAttributes($eleSheet);
                            ++$oldSheetId;

                            // Check if sheet should be skipped
                            if (is_array($this->loadSheetsOnly) && !in_array((string) $eleSheetAttr['name'], $this->loadSheetsOnly)) {
                                ++$countSkippedSheets;
                                $mapSheetId[$oldSheetId] = null;

                                continue;
                            }

                            $sheetReferenceId = (string) self::getArrayItem(self::getAttributes($eleSheet, $xmlNamespaceBase), 'id');
                            if (isset($worksheets[$sheetReferenceId]) === false) {
                                ++$countSkippedSheets;
                                $mapSheetId[$oldSheetId] = null;

                                continue;
                            }
                            // Map old sheet id in original workbook to new sheet id.
                            // They will differ if loadSheetsOnly() is being used
                            $mapSheetId[$oldSheetId] = $oldSheetId - $countSkippedSheets;

                            // Load sheet
                            $docSheet = $excel->createSheet();
                            //    Use false for $updateFormulaCellReferences to prevent adjustment of worksheet
                            //        references in formula cells... during the load, all formulae should be correct,
                            //        and we're simply bringing the worksheet name in line with the formula, not the
                            //        reverse
                            $docSheet->setTitle((string) $eleSheetAttr['name'], false, false);

                            $fileWorksheet = (string) $worksheets[$sheetReferenceId];
                            $xmlSheet = $this->loadZipNoNamespace("$dir/$fileWorksheet", $mainNS);
                            $xmlSheetNS = $this->loadZip("$dir/$fileWorksheet", $mainNS);

                            $sharedFormulas = [];

                            if (isset($eleSheetAttr['state']) && (string) $eleSheetAttr['state'] != '') {
                                $docSheet->setSheetState((string) $eleSheetAttr['state']);
                            }
                            if ($xmlSheetNS) {
                                $xmlSheetMain = $xmlSheetNS->children($mainNS);
                                // Setting Conditional Styles adjusts selected cells, so we need to execute this
                                //    before reading the sheet view data to get the actual selected cells
                                if (!$this->readDataOnly && ($xmlSheet->conditionalFormatting)) {
                                    (new ConditionalStyles($docSheet, $xmlSheet, $dxfs))->load();
                                }
                                if (!$this->readDataOnly && $xmlSheet->extLst) {
                                    (new ConditionalStyles($docSheet, $xmlSheet, $dxfs))->loadFromExt($this->styleReader);
                                }
                                if (isset($xmlSheetMain->sheetViews, $xmlSheetMain->sheetViews->sheetView)) {
                                    $sheetViews = new SheetViews($xmlSheetMain->sheetViews->sheetView, $docSheet);
                                    $sheetViews->load();
                                }

                                $sheetViewOptions = new SheetViewOptions($docSheet, $xmlSheet);
                                $sheetViewOptions->load($this->getReadDataOnly(), $this->styleReader);

                                (new ColumnAndRowAttributes($docSheet, $xmlSheet))
                                    ->load($this->getReadFilter(), $this->getReadDataOnly());
                            }

                            if ($xmlSheetNS && $xmlSheetNS->sheetData && $xmlSheetNS->sheetData->row) {
                                $cIndex = 1; // Cell Start from 1
                                foreach ($xmlSheetNS->sheetData->row as $row) {
                                    $rowIndex = 1;
                                    foreach ($row->c as $c) {
                                        $cAttr = self::getAttributes($c);
                                        $r = (string) $cAttr['r'];
                                        if ($r == '') {
                                            $r = Coordinate::stringFromColumnIndex($rowIndex) . $cIndex;
                                        }
                                        $cellDataType = (string) $cAttr['t'];
                                        $value = null;
                                        $calculatedValue = null;

                                        // Read cell?
                                        if ($this->getReadFilter() !== null) {
                                            $coordinates = Coordinate::coordinateFromString($r);

                                            if (!$this->getReadFilter()->readCell($coordinates[0], (int) $coordinates[1], $docSheet->getTitle())) {
                                                if (isset($cAttr->f)) {
                                                    $this->castToFormula($c, $r, $cellDataType, $value, $calculatedValue, $sharedFormulas, 'castToError');
                                                }
                                                ++$rowIndex;

                                                continue;
                                            }
                                        }

                                        // Read cell!
                                        switch ($cellDataType) {
                                            case 's':
                                                if ((string) $c->v != '') {
                                                    $value = $sharedStrings[(int) ($c->v)];

                                                    if ($value instanceof RichText) {
                                                        $value = clone $value;
                                                    }
                                                } else {
                                                    $value = '';
                                                }

                                                break;
                                            case 'b':
                                                if (!isset($c->f)) {
                                                    if (isset($c->v)) {
                                                        $value = self::castToBoolean($c);
                                                    } else {
                                                        $value = null;
                                                        $cellDataType = DATATYPE::TYPE_NULL;
                                                    }
                                                } else {
                                                    // Formula
                                                    $this->castToFormula($c, $r, $cellDataType, $value, $calculatedValue, $sharedFormulas, 'castToBoolean');
                                                    if (isset($c->f['t'])) {
                                                        $att = $c->f;
                                                        $docSheet->getCell($r)->setFormulaAttributes($att);
                                                    }
                                                }

                                                break;
                                            case 'inlineStr':
                                                if (isset($c->f)) {
                                                    $this->castToFormula($c, $r, $cellDataType, $value, $calculatedValue, $sharedFormulas, 'castToError');
                                                } else {
                                                    $value = $this->parseRichText($c->is);
                                                }

                                                break;
                                            case 'e':
                                                if (!isset($c->f)) {
                                                    $value = self::castToError($c);
                                                } else {
                                                    // Formula
                                                    $this->castToFormula($c, $r, $cellDataType, $value, $calculatedValue, $sharedFormulas, 'castToError');
                                                }

                                                break;
                                            default:
                                                if (!isset($c->f)) {
                                                    $value = self::castToString($c);
                                                } else {
                                                    // Formula
                                                    $this->castToFormula($c, $r, $cellDataType, $value, $calculatedValue, $sharedFormulas, 'castToString');
                                                    if (isset($c->f['t'])) {
                                                        $attributes = $c->f['t'];
                                                        $docSheet->getCell($r)->setFormulaAttributes(['t' => (string) $attributes]);
                                                    }
                                                }

                                                break;
                                        }

                                        // read empty cells or the cells are not empty
                                        if ($this->readEmptyCells || ($value !== null && $value !== '')) {
                                            // Rich text?
                                            if ($value instanceof RichText && $this->readDataOnly) {
                                                $value = $value->getPlainText();
                                            }

                                            $cell = $docSheet->getCell($r);
                                            // Assign value
                                            if ($cellDataType != '') {
                                                // it is possible, that datatype is numeric but with an empty string, which result in an error
                                                if ($cellDataType === DataType::TYPE_NUMERIC && ($value === '' || $value === null)) {
                                                    $cellDataType = DataType::TYPE_NULL;
                                                }
                                                if ($cellDataType !== DataType::TYPE_NULL) {
                                                    $cell->setValueExplicit($value, $cellDataType);
                                                }
                                            } else {
                                                $cell->setValue($value);
                                            }
                                            if ($calculatedValue !== null) {
                                                $cell->setCalculatedValue($calculatedValue);
                                            }

                                            // Style information?
                                            if ($cAttr['s'] && !$this->readDataOnly) {
                                                // no style index means 0, it seems
                                                $cell->setXfIndex(isset($styles[(int) ($cAttr['s'])]) ?
                                                    (int) ($cAttr['s']) : 0);
                                            }
                                        }
                                        ++$rowIndex;
                                    }
                                    ++$cIndex;
                                }
                            }

                            $aKeys = ['sheet', 'objects', 'scenarios', 'formatCells', 'formatColumns', 'formatRows', 'insertColumns', 'insertRows', 'insertHyperlinks', 'deleteColumns', 'deleteRows', 'selectLockedCells', 'sort', 'autoFilter', 'pivotTables', 'selectUnlockedCells'];
                            if (!$this->readDataOnly && $xmlSheet && $xmlSheet->sheetProtection) {
                                foreach ($aKeys as $key) {
                                    $method = 'set' . ucfirst($key);
                                    $docSheet->getProtection()->$method(self::boolean((string) $xmlSheet->sheetProtection[$key]));
                                }
                            }

                            if ($xmlSheet) {
                                $this->readSheetProtection($docSheet, $xmlSheet);
                            }

                            if ($this->readDataOnly === false) {
                                $this->readAutoFilterTables($xmlSheet, $docSheet, $dir, $fileWorksheet, $zip);
                            }

                            if ($xmlSheet && $xmlSheet->mergeCells && $xmlSheet->mergeCells->mergeCell && !$this->readDataOnly) {
                                foreach ($xmlSheet->mergeCells->mergeCell as $mergeCell) {
                                    $mergeRef = (string) $mergeCell['ref'];
                                    if (strpos($mergeRef, ':') !== false) {
                                        $docSheet->mergeCells((string) $mergeCell['ref'], Worksheet::MERGE_CELL_CONTENT_HIDE);
                                    }
                                }
                            }

                            if ($xmlSheet && !$this->readDataOnly) {
                                $unparsedLoadedData = (new PageSetup($docSheet, $xmlSheet))->load($unparsedLoadedData);
                            }

                            if ($xmlSheet !== false && isset($xmlSheet->extLst, $xmlSheet->extLst->ext, $xmlSheet->extLst->ext['uri']) && ($xmlSheet->extLst->ext['uri'] == '{CCE6A557-97BC-4b89-ADB6-D9C93CAAB3DF}')) {
                                // Create dataValidations node if does not exists, maybe is better inside the foreach ?
                                if (!$xmlSheet->dataValidations) {
                                    $xmlSheet->addChild('dataValidations');
                                }

                                foreach ($xmlSheet->extLst->ext->children('x14', true)->dataValidations->dataValidation as $item) {
                                    $node = self::testSimpleXml($xmlSheet->dataValidations)->addChild('dataValidation');
                                    foreach ($item->attributes() ?? [] as $attr) {
                                        $node->addAttribute($attr->getName(), $attr);
                                    }
                                    $node->addAttribute('sqref', $item->children('xm', true)->sqref);
                                    $node->addChild('formula1', $item->formula1->children('xm', true)->f);
                                }
                            }

                            if ($xmlSheet && $xmlSheet->dataValidations && !$this->readDataOnly) {
                                (new DataValidations($docSheet, $xmlSheet))->load();
                            }

                            // unparsed sheet AlternateContent
                            if ($xmlSheet && !$this->readDataOnly) {
                                $mc = $xmlSheet->children(Namespaces::COMPATIBILITY);
                                if ($mc->AlternateContent) {
                                    foreach ($mc->AlternateContent as $alternateContent) {
                                        $alternateContent = self::testSimpleXml($alternateContent);
                                        $unparsedLoadedData['sheets'][$docSheet->getCodeName()]['AlternateContents'][] = $alternateContent->asXML();
                                    }
                                }
                            }

                            // Add hyperlinks
                            if (!$this->readDataOnly) {
                                $hyperlinkReader = new Hyperlinks($docSheet);
                                // Locate hyperlink relations
                                $relationsFileName = dirname("$dir/$fileWorksheet") . '/_rels/' . basename($fileWorksheet) . '.rels';
                                if ($zip->locateName($relationsFileName)) {
                                    $relsWorksheet = $this->loadZip($relationsFileName, Namespaces::RELATIONSHIPS);
                                    $hyperlinkReader->readHyperlinks($relsWorksheet);
                                }

                                // Loop through hyperlinks
                                if ($xmlSheetNS && $xmlSheetNS->children($mainNS)->hyperlinks) {
                                    $hyperlinkReader->setHyperlinks($xmlSheetNS->children($mainNS)->hyperlinks);
                                }
                            }

                            // Add comments
                            $comments = [];
                            $vmlComments = [];
                            if (!$this->readDataOnly) {
                                // Locate comment relations
                                $commentRelations = dirname("$dir/$fileWorksheet") . '/_rels/' . basename($fileWorksheet) . '.rels';
                                if ($zip->locateName($commentRelations)) {
                                    $relsWorksheet = $this->loadZip($commentRelations, Namespaces::RELATIONSHIPS);
                                    foreach ($relsWorksheet->Relationship as $elex) {
                                        $ele = self::getAttributes($elex);
                                        if ($ele['Type'] == Namespaces::COMMENTS) {
                                            $comments[(string) $ele['Id']] = (string) $ele['Target'];
                                        }
                                        if ($ele['Type'] == Namespaces::VML) {
                                            $vmlComments[(string) $ele['Id']] = (string) $ele['Target'];
                                        }
                                    }
                                }

                                // Loop through comments
                                foreach ($comments as $relName => $relPath) {
                                    // Load comments file
                                    $relPath = File::realpath(dirname("$dir/$fileWorksheet") . '/' . $relPath);
                                    // okay to ignore namespace - using xpath
                                    $commentsFile = $this->loadZip($relPath, '');

                                    // Utility variables
                                    $authors = [];
                                    $commentsFile->registerXpathNamespace('com', $mainNS);
                                    $authorPath = self::xpathNoFalse($commentsFile, 'com:authors/com:author');
                                    foreach ($authorPath as $author) {
                                        $authors[] = (string) $author;
                                    }

                                    // Loop through contents
                                    $contentPath = self::xpathNoFalse($commentsFile, 'com:commentList/com:comment');
                                    foreach ($contentPath as $comment) {
                                        $commentx = $comment->attributes();
                                        $commentModel = $docSheet->getComment((string) $commentx['ref']);
                                        if (isset($commentx['authorId'])) {
                                            $commentModel->setAuthor($authors[(int) $commentx['authorId']]);
                                        }
                                        $commentModel->setText($this->parseRichText($comment->children($mainNS)->text));
                                    }
                                }

                                // later we will remove from it real vmlComments
                                $unparsedVmlDrawings = $vmlComments;

                                // Loop through VML comments
                                foreach ($vmlComments as $relName => $relPath) {
                                    // Load VML comments file
                                    $relPath = File::realpath(dirname("$dir/$fileWorksheet") . '/' . $relPath);

                                    try {
                                        // no namespace okay - processed with Xpath
                                        $vmlCommentsFile = $this->loadZip($relPath, '');
                                        $vmlCommentsFile->registerXPathNamespace('v', Namespaces::URN_VML);
                                    } catch (Throwable $ex) {
                                        //Ignore unparsable vmlDrawings. Later they will be moved from $unparsedVmlDrawings to $unparsedLoadedData
                                        continue;
                                    }

                                    // Locate VML drawings image relations
                                    $drowingImages = [];
                                    $VMLDrawingsRelations = dirname($relPath) . '/_rels/' . basename($relPath) . '.rels';
                                    if ($zip->locateName($VMLDrawingsRelations)) {
                                        $relsVMLDrawing = $this->loadZip($VMLDrawingsRelations, Namespaces::RELATIONSHIPS);
                                        foreach ($relsVMLDrawing->Relationship as $elex) {
                                            $ele = self::getAttributes($elex);
                                            if ($ele['Type'] == Namespaces::IMAGE) {
                                                $drowingImages[(string) $ele['Id']] = (string) $ele['Target'];
                                            }
                                        }
                                    }

                                    $shapes = self::xpathNoFalse($vmlCommentsFile, '//v:shape');
                                    foreach ($shapes as $shape) {
                                        $shape->registerXPathNamespace('v', Namespaces::URN_VML);

                                        if (isset($shape['style'])) {
                                            $style = (string) $shape['style'];
                                            $fillColor = strtoupper(substr((string) $shape['fillcolor'], 1));
                                            $column = null;
                                            $row = null;
                                            $fillImageRelId = null;
                                            $fillImageTitle = '';

                                            $clientData = $shape->xpath('.//x:ClientData');
                                            if (is_array($clientData) && !empty($clientData)) {
                                                $clientData = $clientData[0];

                                                if (isset($clientData['ObjectType']) && (string) $clientData['ObjectType'] == 'Note') {
                                                    $temp = $clientData->xpath('.//x:Row');
                                                    if (is_array($temp)) {
                                                        $row = $temp[0];
                                                    }

                                                    $temp = $clientData->xpath('.//x:Column');
                                                    if (is_array($temp)) {
                                                        $column = $temp[0];
                                                    }
                                                }
                                            }

                                            $fillImageRelNode = $shape->xpath('.//v:fill/@o:relid');
                                            if (is_array($fillImageRelNode) && !empty($fillImageRelNode)) {
                                                $fillImageRelNode = $fillImageRelNode[0];

                                                if (isset($fillImageRelNode['relid'])) {
                                                    $fillImageRelId = (string) $fillImageRelNode['relid'];
                                                }
                                            }

                                            $fillImageTitleNode = $shape->xpath('.//v:fill/@o:title');
                                            if (is_array($fillImageTitleNode) && !empty($fillImageTitleNode)) {
                                                $fillImageTitleNode = $fillImageTitleNode[0];

                                                if (isset($fillImageTitleNode['title'])) {
                                                    $fillImageTitle = (string) $fillImageTitleNode['title'];
                                                }
                                            }

                                            if (($column !== null) && ($row !== null)) {
                                                // Set comment properties
                                                $comment = $docSheet->getCommentByColumnAndRow($column + 1, $row + 1);
                                                $comment->getFillColor()->setRGB($fillColor);
                                                if (isset($drowingImages[$fillImageRelId])) {
                                                    $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                                    $objDrawing->setName($fillImageTitle);
                                                    $imagePath = str_replace('../', 'xl/', $drowingImages[$fillImageRelId]);
                                                    $objDrawing->setPath(
                                                        'zip://' . File::realpath($filename) . '#' . $imagePath,
                                                        true,
                                                        $zip
                                                    );
                                                    $comment->setBackgroundImage($objDrawing);
                                                }

                                                // Parse style
                                                $styleArray = explode(';', str_replace(' ', '', $style));
                                                foreach ($styleArray as $stylePair) {
                                                    $stylePair = explode(':', $stylePair);

                                                    if ($stylePair[0] == 'margin-left') {
                                                        $comment->setMarginLeft($stylePair[1]);
                                                    }
                                                    if ($stylePair[0] == 'margin-top') {
                                                        $comment->setMarginTop($stylePair[1]);
                                                    }
                                                    if ($stylePair[0] == 'width') {
                                                        $comment->setWidth($stylePair[1]);
                                                    }
                                                    if ($stylePair[0] == 'height') {
                                                        $comment->setHeight($stylePair[1]);
                                                    }
                                                    if ($stylePair[0] == 'visibility') {
                                                        $comment->setVisible($stylePair[1] == 'visible');
                                                    }
                                                }

                                                unset($unparsedVmlDrawings[$relName]);
                                            }
                                        }
                                    }
                                }

                                // unparsed vmlDrawing
                                if ($unparsedVmlDrawings) {
                                    foreach ($unparsedVmlDrawings as $rId => $relPath) {
                                        $rId = substr($rId, 3); // rIdXXX
                                        $unparsedVmlDrawing = &$unparsedLoadedData['sheets'][$docSheet->getCodeName()]['vmlDrawings'];
                                        $unparsedVmlDrawing[$rId] = [];
                                        $unparsedVmlDrawing[$rId]['filePath'] = self::dirAdd("$dir/$fileWorksheet", $relPath);
                                        $unparsedVmlDrawing[$rId]['relFilePath'] = $relPath;
                                        $unparsedVmlDrawing[$rId]['content'] = $this->securityScanner->scan($this->getFromZipArchive($zip, $unparsedVmlDrawing[$rId]['filePath']));
                                        unset($unparsedVmlDrawing);
                                    }
                                }

                                // Header/footer images
                                if ($xmlSheet && $xmlSheet->legacyDrawingHF) {
                                    if ($zip->locateName(dirname("$dir/$fileWorksheet") . '/_rels/' . basename($fileWorksheet) . '.rels')) {
                                        $relsWorksheet = $this->loadZipNoNamespace(dirname("$dir/$fileWorksheet") . '/_rels/' . basename($fileWorksheet) . '.rels', Namespaces::RELATIONSHIPS);
                                        $vmlRelationship = '';

                                        foreach ($relsWorksheet->Relationship as $ele) {
                                            if ($ele['Type'] == Namespaces::VML) {
                                                $vmlRelationship = self::dirAdd("$dir/$fileWorksheet", $ele['Target']);
                                            }
                                        }

                                        if ($vmlRelationship != '') {
                                            // Fetch linked images
                                            $relsVML = $this->loadZipNoNamespace(dirname($vmlRelationship) . '/_rels/' . basename($vmlRelationship) . '.rels', Namespaces::RELATIONSHIPS);
                                            $drawings = [];
                                            if (isset($relsVML->Relationship)) {
                                                foreach ($relsVML->Relationship as $ele) {
                                                    if ($ele['T