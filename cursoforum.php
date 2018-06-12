<?php
  // Importo Moodle e configuraÃ§Ãµes
  require_once(dirname(__FILE__) . '/config.php');
  $url = $_COOKIE['SingleSignOn'];
  setcookie("SingleSignOn", "", time() - 3600);
?>
<html lang="en">
<head>
    <title>Curso FÃ³rum</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30; url=<?php echo $url;?>">
    <link href="<?php echo new moodle_url('/theme/grupogen/style/login_min.css'); ?>" rel='stylesheet' type='text/css'>
    <link rel="icon" type="image/png" href="<?php echo new moodle_url('/theme/grupogen/pix/favicon.ico'); ?>">
    <style>
      body {
        font-size:22px;
      }
      #login {
        background-color: #fff;
      }
      h1 {
        font-weight: bold;
      }
    </style>
</head>
<body id="login" class="container-fluid">
  <div class="container">
    <div class="row">
      <h1 class="text-center">Curso Forum informa!</h1>
    </div>
    <div class="row">
      <p class="text-justify">Se vocÃª estÃ¡ vendo esta mensagem, significa que o seu login foi utilizado simultaneamente ou o AVA ultrapassou o tempo limite de inatividade.</p>
      <p class="text-justify">Para reestabelecer o acesso, feche todas as abas de seu navegador e realize o login novamente.</p>
      <p class="text-justify">Caso a dificuldade continue sendo reproduzida apÃ³s essas verificaÃ§Ãµes,  entre em contato com o nosso atendimento atravÃ©s do e-mail <a href="mailto:atendimento@cursoforum.com.br">atendimento@cursoforum.com.br</a>.</p>
      <p class="text-justify">Caso nÃ£o seja redirecionado automaticamente <a href="<?php echo $url;?>">clique aqui</a></p>
    </div>
  </div>
</div>
</body>
</html>
