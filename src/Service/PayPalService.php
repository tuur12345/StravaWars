namespace App\Service;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaypalService
{
private $apiContext;

public function __construct(string $clientId, string $clientSecret, string $environment)
{
$this->apiContext = new ApiContext(
new OAuthTokenCredential($clientId, $clientSecret)
);

$this->apiContext->setConfig(['mode' => $environment]);
}

public function getApiContext(): ApiContext
{
return $this->apiContext;
}
}
