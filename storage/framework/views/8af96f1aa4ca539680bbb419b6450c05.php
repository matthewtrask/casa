<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Casa')); ?> - <?php echo $__env->yieldContent('title', 'Household Manager'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        nav {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }

        nav a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
            margin-right: 2rem;
            font-size: 1.2rem;
        }

        nav a:hover {
            color: #22863a;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #22863a;
        }

        .header a {
            background: #22863a;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .header a:hover {
            background: #1a6b2a;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #22863a;
            box-shadow: 0 0 0 3px rgba(34, 134, 58, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
        }

        button, .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary, button[type="submit"]:not(.btn-danger):not(.btn-secondary):not(.btn-info):not(.btn-warning):not(.delete-link):not(.btn-water):not(.btn-water-large):not(.note-toggle) {
            background: #22863a;
            color: white;
        }

        .btn-primary:hover, button[type="submit"]:not(.btn-danger):not(.btn-secondary):not(.btn-info):not(.btn-warning):not(.delete-link):not(.btn-water):not(.btn-water-large):not(.note-toggle):hover {
            background: #1a6b2a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        <?php echo $__env->yieldContent('styles'); ?>
    </style>
</head>
<body>
    <nav style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 2rem; align-items: center;">
            <a href="/" style="font-size: 1.4rem; font-weight: 700;">🏠 <?php echo e(config('app.name', 'Casa')); ?></a>
            <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
            <a href="<?php echo e(route('items.index', ['category' => 'plant'])); ?>">🌿 Plants</a>
            <a href="<?php echo e(route('items.index', ['category' => 'chore'])); ?>">🧹 Chores</a>
            <a href="<?php echo e(route('items.index', ['category' => 'maintenance'])); ?>">🔧 Maintenance</a>
            <a href="<?php echo e(route('items.index', ['category' => 'pet'])); ?>">🐾 Pets</a>
        </div>
        <a href="<?php echo e(route('items.create')); ?>" style="background: #22863a; color: white; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 600; text-decoration: none;">+ Add Item</a>
    </nav>

    <div class="container">
        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <strong>Oops! Something went wrong:</strong>
                <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>