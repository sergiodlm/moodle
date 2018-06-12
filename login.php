<?php

  if (isset($_COOKIE['SingleSignOn']) && !empty($_COOKIE['SingleSignOn'])) {    
    header("Location: /cursoforum.php");
  }
  // Importo Moodle e configurações
  require_once(dirname(__FILE__) . '/config.php');

  // Código do erro de login, se existir
  $loginError = optional_param('errorcode', 0, PARAM_INT);

  // Mensagens dos erros de login
  $loginMessages = array(
      1 => get_string('cookiesnotenabled'),
      2 => get_string('invalidusername'),
      3 => get_string('invalidlogin'),
      4 => get_string('sessionerroruser', 'error'),
  );
  // raffle bg
  $bg['1'] = 'theme/grupogen/pix/login/banner-1.jpg';
  $bg['2'] = 'theme/grupogen/pix/login/banner-2.jpg';
  $bg['3'] = 'theme/grupogen/pix/login/banner-3.jpg';
  $raffle = rand(1,3);

?>
<!doctype html>
<html lang="en">
<head>
    <title>Login AVA</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo new moodle_url('/theme/grupogen/style/login_min.css'); ?>" rel='stylesheet' type='text/css'>
    <link rel="icon" type="image/png" href="<?php echo new moodle_url('/theme/grupogen/pix/favicon.ico'); ?>">
</head>
<body id="login" class="container-fluid">
    <!-- raffle bg -->
    <img src=" <?php echo "$bg[$raffle]"; ?> " alt="" class="bg_login hidden-xs"/>
    <!-- box login -->
    <div class="box_login">
      <header>
        <span>Login</span>
      </header>
      <form action="<?php echo new moodle_url('/login/index.php'); ?>" method="post">
          <i class="icon user"></i>
          <input type="text" name="username" id="username" value="" placeholder="digite o usuário" class="input">
          <i class="icon password"></i>
          <input type="password" name="password" id="password" value="" placeholder="digite a senha" class="input">
          <div class="col-xs-6 option">
            <a href="<?php echo new moodle_url('/login/forgot_password.php')?>" title="Esqueceu a senha?" class="forget">Esqueceu a senha?</a>
          </div>
          <input type="submit" value="Entrar" title="entrar" class="button">
          <a href="<?php echo new moodle_url('/login/signup.php')?>" class="button signup">Cadastre-se</a>
      </form>
      <!-- mensaggem de usuário ou senha incorreta -->
      <?php if ($loginError && array_key_exists($loginError, $loginMessages)): ?>
        <div class="alert-box" data-alert>
         <?php echo $loginMessages[$loginError]; ?>
        </div>
      <?php endif; ?>
    </div>
</body>
</html>
