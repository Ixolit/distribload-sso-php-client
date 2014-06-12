<?php
use DistribLoad\SSO\SSOClient;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>DistribLoad SSO test</title>
	</head>

	<body>
		<form action="" method="post">
			<label for="endpoint">Endpoint:</label> <input type="text" id="endpoint" name="endpoint" value="<?=
				(isset($_REQUEST['endpoint'])?htmlspecialchars($_REQUEST['endpoint']):'') ?>" /><br />
			<label for="apikey">API key:</label> <input type="text" id="apikey" name="apikey" value="<?=
				(isset($_REQUEST['apikey'])?htmlspecialchars($_REQUEST['apikey']):'') ?>" /><br />
			<label for="apisecret">API secret:</label> <input type="text" id="apisecret" name="apisecret" value="<?=
				(isset($_REQUEST['apisecret'])?htmlspecialchars($_REQUEST['apisecret']):'') ?>" /><br />
			<label for="token">Login token:</label> <input type="text" id="token" name="token" value="<?=
				(isset($_REQUEST['token'])?htmlspecialchars($_REQUEST['token']):'') ?>" /><br />
			<input type="submit" name="action" value="Start SSO login" />
			<input type="submit" name="action" value="Finalise SSO login" />
		</form>

		<?php
		function __autoload($class) {
			include __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
		}

		if (isset($_REQUEST['endpoint']) && isset($_REQUEST['apikey']) && isset($_REQUEST['apisecret'])) {
			$client = new SSOClient($_REQUEST['endpoint'], $_REQUEST['apikey'], $_REQUEST['apisecret']);
			if (isset($_REQUEST['action'])) {
				switch ($_REQUEST['action']) {
					case 'Start SSO login':
						echo('<a href="' . htmlspecialchars($client->startLogin('http://' . $_SERVER['HTTP_HOST'] .
								$_SERVER['REQUEST_URI'])) . '">Click here to start login process</a>');
						break;
					case 'Finalise SSO login':
						if (isset($_REQUEST['token'])) {
							try {
								var_dump($client->finaliseLogin($_REQUEST['token']));
							} catch (Exception $e) {
								var_dump($e);
							}
						}
						break;
				}
			}
		}
		?>
	</body>
</html>
