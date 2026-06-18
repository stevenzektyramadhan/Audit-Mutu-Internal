<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" integrity="sha512-P5MgXuoE..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Login AMI</h4>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                    <?php endif; ?>
                    <?php echo form_open('auth/login'); ?>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo set_value('email'); ?>" required>
                            <?php echo form_error('email', '<small class="text-danger">', '</small>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <?php echo form_error('password', '<small class="text-danger">', '</small>'); ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
