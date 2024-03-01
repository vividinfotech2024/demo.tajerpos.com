<form method="post" action="<?php echo e(route('set-language')); ?>">
<?php echo csrf_field(); ?>
    <div class="form-group text-end">
        <select name="site_language" class="site-language" onchange="this.form.submit()">
            <option value="en" <?php echo e(app()->getLocale() === 'en' ? 'selected' : ''); ?>>English</option>
            <option value="ar" <?php echo e(app()->getLocale() === 'ar' ? 'selected' : ''); ?>>Arabic</option>
        </select>
    </div>
</form>
<?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/language.blade.php ENDPATH**/ ?>