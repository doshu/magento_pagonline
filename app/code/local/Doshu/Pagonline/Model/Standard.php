<?php  
    class Doshu_Pagonline_Model_Standard extends Mage_Payment_Model_Method_Abstract {  
    
  		protected $_code = 'pagonline';
		protected $_isGateway               = true;
		protected $_canAuthorize            = true;
		protected $_canCapture              = true;
		protected $_canCapturePartial       = false;
		protected $_canRefund               = false;
		protected $_canVoid                 = true;
		protected $_canUseInternal          = true;
		protected $_canUseCheckout          = true;
		protected $_canUseForMultishipping  = true;
		protected $_canSaveCc = false;
		protected $_isInitializeNeeded      = true;

		protected $_formBlockType = 'pagonline/form';
    	protected $_infoBlockType = 'pagonline/info';

		public function getOrderPlaceRedirectUrl() {
			//when you click on place order you will be redirected on this url, if you don't want this action remove this method
			return Mage::getUrl('pagonline/standard/redirect', array('_secure' => true));
		}
		
		public function getPaymentUrl($order) {
		
			$orderData = $order->getData();
		
			$numeroCommerciante = Mage::getStoreConfig('payment/pagonline/numero_commerciante');
			$stabilimento = Mage::getStoreConfig('payment/pagonline/stabilimento');
			$userID = Mage::getStoreConfig('payment/pagonline/userid');
			$password = Mage::getStoreConfig('payment/pagonline/password');
			$numeroOrdine = $order->getRealOrderId();
			$totaleOrdine = str_replace('.', '', number_format($order->getGrandTotal(), 2, '.', ''));
			$valuta = 978;
			$flagRiciclaOrdine = 'Y';
			$flagDeposito = 'N';
			$tipoRispostaApv = 'wait';
			$urlOk = Mage::getStoreConfig('payment/pagonline/urlok');
			$urlKo = Mage::getStoreConfig('payment/pagonline/urlko');

			$stringaSegreta = Mage::getStoreConfig('payment/pagonline/stringa_segreta');

			// Concatenazione input per il calcolo del MAC
			$inputMac  = "numeroCommerciante=".trim($numeroCommerciante);
			$inputMac .= "&stabilimento=".trim($stabilimento);
			$inputMac .= "&userID=".trim($userID);
			$inputMac .= "&password=".trim($password);
			$inputMac .= "&numeroOrdine=".trim($numeroOrdine);
			$inputMac .= "&totaleOrdine=".trim($totaleOrdine);
			$inputMac .= "&valuta=".trim($valuta);
			$inputMac .= "&flagRiciclaOrdine=".trim($flagRiciclaOrdine);
			$inputMac .= "&flagDeposito=".trim($flagDeposito);
			$inputMac .= "&tipoRispostaApv=".trim($tipoRispostaApv);
			$inputMac .= "&urlOk=".trim($urlOk);
			$inputMac .= "&urlKo=".trim($urlKo);

			$inputMac .= "&".trim($stringaSegreta);
			

			//Calcolo della firma digitale della stringa in input
			$MAC = md5($inputMac);
			$MACtemp = "";
			for($i=0;$i<strlen($MAC);$i=$i+2) {
				$MACtemp .= chr(hexdec(substr($MAC,$i,2)));
			}
			$MAC = $MACtemp;

			// Codifica del MAC con lo standard BASE64
			$MACcode = base64_encode($MAC);

			// Concatenazione input per URL di USI
			$inputUrl = Mage::getStoreConfig('payment/pagonline/service_url');
			$inputUrl .= "?numeroCommerciante=".trim($numeroCommerciante);
			$inputUrl .= "&stabilimento=".trim($stabilimento);
			$inputUrl .= "&userID=".trim($userID);
			$inputUrl .= "&password=Password";      //la password vera viene usata solo per il calcolo del MAC e non viene inviata al sito dei pagamenti (qui è sostituita con il valore fittizio "Password")
			$inputUrl .= "&numeroOrdine=".trim($numeroOrdine);
			$inputUrl .= "&totaleOrdine=".trim($totaleOrdine);
			$inputUrl .= "&valuta=".trim($valuta);
			$inputUrl .= "&flagRiciclaOrdine=".trim($flagRiciclaOrdine);
			$inputUrl .= "&flagDeposito=".trim($flagDeposito);
			$inputUrl .= "&tipoRispostaApv=".trim($tipoRispostaApv);
			$inputUrl .= "&urlOk=".urlencode(trim($urlOk));
			$inputUrl .= "&urlKo=".urlencode(trim($urlKo));
			$inputUrl .= "&mac=".urlencode(trim($MACcode));
			
			return $inputUrl;
		}
		
		
		public function isSuccessResponse($request) {
			
			$secretString = Mage::getStoreConfig('payment/pagonline/stringa_segreta');
			$numeroCommerciante = $request->getPost('numeroCommerciante');
			$stabilimento = $request->getPost('stabilimento');
			$esito = $request->getPost('esito');
			$numeroOrdine = $request->getPost('numeroOrdine');
			$dataApprovazione = $request->getPost('dataApprovazione');
			$mac = $request->getPost('mac');
	
			$inputMac  = "numeroOrdine=".trim($numeroOrdine);
			$inputMac .= "&numeroCommerciante=".trim($numeroCommerciante);
			$inputMac .= "&stabilimento=".trim($stabilimento);
			$inputMac .= "&esito=".trim($esito);
			$inputMac .= "&dataApprovazione=".trim($dataApprovazione);
	
			//Calcolo della firma digitale della stringa in input
			$MAC = md5($inputMac."&".$secretString);
			$MACtemp = "";
			for($i=0;$i<strlen($MAC);$i=$i+2) {
			  $MACtemp .= chr(hexdec(substr($MAC,$i,2)));
			}
			$MAC = $MACtemp;
			// Codifica del MAC con lo standard BASE64
			$MACcode = base64_encode($MAC);
	
			return ($mac==$MACcode);
			  
		}

    }  
