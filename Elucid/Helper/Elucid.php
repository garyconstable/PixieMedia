<?php

//// THIS MODULE REQUIRES SALES ORDER ATTRIBUTE: elucid_soc
//// AND CUSTOMER ATTRIBUTE: elucid_urn

namespace PixieMedia\Elucid\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

class Elucid extends AbstractHelper
{

    /**
     * @param Context $context
     */
    private $httpContext;
    protected $orderFactory;
    protected $customerRepositoryInterface;
    protected $customer;
    protected $varFactory;
    protected $_storeManager;
    protected $_currency;
    protected $elucidOrderFactory;
    protected $elucidOrderObject;
    protected $resource;
    protected $rule;
    protected $taxItem;
    protected $soapClientFactory;
    //protected $elucidBrandFactory;
    //protected $elucidAgentFactory;
    //protected $elucidCustomergroupFactory;
    //protected $elucidStatusFactory;
    protected $subscriber;
    //protected $elucidShippingFactory;
    protected $apiOrder;
    // protected $amAdapter;
    // protected $amResolver;
    // protected $amForm;
    protected $_escaper;
    protected $converter;
    protected $shipNotifier;
    protected $_dir;
    protected $logger;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Directory\Model\Currency $_currency,
        \PixieMedia\Elucid\Model\ResourceModel\Order\CollectionFactory $elucidOrderFactory,
        \PixieMedia\Elucid\Model\Order $elucidOrderObject,
        // \PixieMedia\Elucid\Model\ResourceModel\Brand\CollectionFactory $elucidBrandFactory,
        // \PixieMedia\Elucid\Model\ResourceModel\Agentmap\CollectionFactory $elucidAgentFactory,
        // \PixieMedia\Elucid\Model\ResourceModel\Shipping\CollectionFactory $elucidShippingFactory,
        // \PixieMedia\Elucid\Model\ResourceModel\CustomerGroup\CollectionFactory $elucidCustomergroupFactory,
        // \PixieMedia\Elucid\Model\ResourceModel\Status\CollectionFactory $elucidStatusFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Sales\Model\ResourceModel\Order\Tax\Item $taxItem,
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Sales\Api\Data\OrderInterface $apiOrder,
        // \Amasty\Orderattr\Model\Entity\Adapter\Order\Adapter $amAdapter,
        // \Amasty\Orderattr\Model\Entity\EntityResolver $amResolver,
        // \Amasty\Orderattr\Model\Value\Metadata\FormFactory $amForm,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Sales\Model\Convert\Order $converter,
        \Magento\Shipping\Model\ShipmentNotifier $shipNotifier,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        array $data = [],
        LoggerInterface $logger
    ) {
        $this->httpContext = $httpContext;
        $this->_orderFactory = $orderFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->customer = $customer;
        $this->_varFactory = $varFactory;
        $this->_storeManager = $_storeManager;
        $this->_currency = $_currency;
        $this->elucidOrderFactory = $elucidOrderFactory;
        $this->elucidOrderObject = $elucidOrderObject;
        // $this->elucidBrandFactory = $elucidBrandFactory;
        // $this->elucidAgentFactory = $elucidAgentFactory;
        // $this->elucidShippingFactory = $elucidShippingFactory;
        // $this->elucidCustomergroupFactory = $elucidCustomergroupFactory;
        // $this->elucidStatusFactory = $elucidStatusFactory;
        $this->resource = $resource;
        $this->rule = $rule;
        $this->taxItem = $taxItem;
        $this->soapClientFactory = $soapClientFactory;
        $this->subscriber = $subscriber;
        $this->apiOrder = $apiOrder;
        // $this->amAdapter = $amAdapter;
        // $this->amResolver = $amResolver;
        // $this->amForm = $amForm;
        $this->_escaper = $_escaper;
        $this->converter = $converter;
        $this->shipNotifier = $shipNotifier;
        $this->_dir = $dir;
        $this->logger = $logger;

        parent::__construct($context);
    }

    public function isElucidDisabled()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/disable_elucid';

        return $this->getConfigValue($path, $storeId);
    }

    public function getElucidWsdl()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/wsdl';

        return $this->getConfigValue($path, $storeId);
    }

    public function getDefaultCustomerClass()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/kclass';

        return $this->getConfigValue($path, $storeId);
    }

    public function getStagingValue()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/alias_staging';

        return $this->getConfigValue($path, $storeId);
    }

    public function getPickingValue()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/alias_picking';

        return $this->getConfigValue($path, $storeId);
    }

    public function getSubscribeEmail()
    {
        $storeId = $this->getStore();
        $path = 'pixieelucid/elucid_misc/subscribe_email';

        return $this->getConfigValue($path, $storeId);
    }

    public function addOrderToQueue($order)
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $elucidOrder = $this->elucidOrderFactory->create()
            ->addFieldToFilter('magento_id', $order->getIncrementId());

        if (count($elucidOrder)) {

            // checks and balances - order was saved again for a reason
            foreach ($elucidOrder as $kOrder) {
                $state = $order->getState();
                if ($state == 'canceled') {
                    $kOrder->setIntegrationStatus("canceled");
                    $kOrder->setMagentoStatus("canceled");
                    $kOrder->save();
                    return;
                }

                // ***** CHECK IF ORDER ALREADY COMPLETE *** //
                if ($state == 'complete') {
                    $kOrder->setIntegrationStatus("complete");
                    $kOrder->setMagentoStatus("complete");
                    $kOrder->save();
                    return;
                }

                $kOrder->setMagentoStatus($state);
                $kOrder->save();
            }
        } else {

            // Lets add it to the queue
            $kOrder = $this->elucidOrderObject;
            $kOrder->setMagentoId($order->getIncrementId());
            $kOrder->setMagentoStatus($order->getState());
            $kOrder->setIntegrationStatus('new');
            $kOrder->setIntegrationCount('0');
            $kOrder->save();
        }

        return true;
    }

    public function processQueue()
    {
        $this->logThis("1. Starting processQueue");
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $this->logThis("2. Starting processQueue");
        $elucidOrders = $this->elucidOrderFactory->create()
            ->addFieldToFilter('integration_status', 'new');

        foreach ($elucidOrders as $kOrder) {
            $this->logThis("3. processQueue: " . $kOrder->getMagentoId());
            $order  = $this->_orderFactory->create()->loadByIncrementId($kOrder->getMagentoId());

            // ***** CHECK ORDER STILL EXISTS ***** //
            if (!$order) {
                $kOrder->setIntegrationStatus("order_not_found");
                $kOrder->save();
                continue;
            }

            // ***** USE STATE FOR CHECKS ***** //
            $state  = $order->getState();

            // ***** CHECK IF ORDER IN CANCELLED ***** //
            if ($state == 'canceled') {
                $kOrder->setIntegrationStatus("canceled");
                $kOrder->setMagentoStatus("canceled");
                $kOrder->save();
                continue;
            }

            // ***** CHECK IF ORDER ALREADY COMPLETE *** //
            if ($state == 'complete') {
                $kOrder->setIntegrationStatus("complete");
                $kOrder->setMagentoStatus("complete");
                $kOrder->save();
                continue;
            }

            // ***** CATCH FOR REPEATED FAILURES ***** //
            $intCount = (int)$kOrder->getIntegrationCount() + 1;
            //echo $intCount.' ';
            $kOrder->setIntegrationCount($intCount);
            $kOrder->save();

            if ($intCount > 12) {
                $kOrder->setIntegrationStatus("errored");
                $kOrder->save();
                continue;
            }

            // ***** CHECK ORDER HAS BEEN PAID ***** //
            $balance = $order->getBaseTotalDue();
            if ($balance > 0) {
                $intCount = (int)$kOrder->getIntegrationCount();
                if ($intCount > 10) {
                    $kOrder->setIntegrationStatus("not paid");
                }
                $kOrder->save();
                continue;
            }

            // ***** MAKE SURE ITS NOT ALREADY IN ***** //
            $korn = $kOrder->getElucidOrdernumber();
            if ($korn && strlen($korn) > 2) {
                $kOrder->setIntegrationStatus("imported");
                $kOrder->save();
                continue;
            }

            // ***** STILL HERE - LETS PREPARE THE ORDER FOR ELUCID ***** //
            $this->addNewOrderToElucid($order, $kOrder);
        }

        return true;
    }

    public function addNewOrderToElucid($order, $kOrder=false)
    {
        $this->logThis("4. addNewOrderToElucid: " . $order->getId());
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $this->logThis('--> after the diabled....');

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        // Get the id of the orders billing address
        $billing_id = $order->getBillingAddress()->getId();
        // Get shipping address data using the id
        $billing_address = $order->getBillingAddress();

        // Get the id of the orders shipping address
        $shipping_id = $order->getShippingAddress()->getId();
        // Get shipping address data using the id
        $shipping_address = $order->getShippingAddress();

        $salesRep = '';
        if ($order->getCouponCode()) {
            $salesRep = $this->resolveSalesrep($order->getCouponCode());
        }

        $subscribed = $this->checkSubscribed($billing_address->getEmail());
        if ($subscribed) {
            $subsc = '-1';
            $mailingStatus = 2;
        } else {
            $subsc = 0;
            $mailingStatus = 3;
        }

        // Start with best foot forward - everything will be fine, we can do this
        $location = $this->getPickingValue();
        //$isNew = 0;
        $newState = $this->getStateByLocation('picking');
        $howHeard = $this->getHowHeard($order);
        $orderNote = $this->getDeliveryNote($order);
        if ($howHeard && strtoupper($howHeard) == 'OTHER') {
            // Customer has free typed how they found us. Change the
            // location and append the 'other' value to the order note.
            $howHeard   = '';
            $location   = $this->getStagingValue();
            $newState   = $this->getStateByLocation('staging');
            $otherHeard = $this->getOtherHeard($order);
            $orderNote  = "Customer choose 'other' with value: $otherHeard \r\n" . $orderNote;
        }

        // The customer may not have a elucid urn  - if not, go through elucid user query/registration
        if ($order->getCustomerId()) {

            // Registered
            $user = $this->customer->load($order->getCustomerId());

            // Query this customer anyway to update the customer class
            $urn = $this->queryElucidCustomer($user, $wsdl, $kOrder);
            if (!$user->getElucidUrn() || strlen($user->getElucidUrn()) < 1) {
                // call function to register the user - if it fails we should stop
                if (!$urn) {
                    //$isNew = '-1';
                    $urn = $this->createElucidCustomer($user, $billing_address, $wsdl, $kOrder, $salesRep, $subsc, true, $howHeard, $mailingStatus);
                }
                if (!$urn) {
                    $this->logThis($user->getEmail() . ': urn code couldnt be defined - ' . $urn);
                    return;
                }
                $user->setElucidUrn($urn);
                // now save it!
                $user->save();
            }

            $email = $user->getEmail();
            $fName = $user->getFirstname();
            $lName = $user->getLastname();
            $oRef  = $user->getId();
            $kclass = $this->getElucidClass($user, $wsdl, $kOrder, $billing_address);
        } else {

            // Guest
            $urn = $this->queryElucidCustomer(false, $wsdl, $kOrder, $billing_address);
            if (!$urn) {
                //$isNew = '-1';
                $urn = $this->createElucidCustomer(false, $billing_address, $wsdl, $kOrder, $salesRep, $subsc, true, $howHeard, $mailingStatus);
            }

            $email = $billing_address->getEmail();
            $fName = $billing_address->getFirstname();
            $lName = $billing_address->getLastname();
            $oRef  = 'GUEST';
            $kclass = $this->getElucidClass(false, $wsdl, $kOrder, $billing_address);
        }

        $this->logThis($fName . ' ' . $lName . ' ' . $email . ': url code - ' . $urn);

        //echo $order->getIncrementId().' ';
        //echo $email.' ';
        //echo $fName.' '.$lName;

        $created = date("Y-m-d G:i:s", strtotime($order->getCreatedAt()));

        // shipping description
        $shipping_desc = $order->getShippingDescription();
        $shipping_desc = explode(' - ', $shipping_desc);
        $shipping_method = $shipping_desc[1];

        // calculate gross shipping amount
        $del_gross = $order->getShippingAmount() + $order->getShippingTaxAmount();

        // Get billing address lines and split if required
        $billing_street  = $billing_address->getStreet();
        $billingLine1 = (isset($billing_street[0])) ? $billing_street[0] : '';
        $billingLine2 = (isset($billing_street[1])) ? $billing_street[1] : '';
        $billingLine3 = '';
        if (strlen($billingLine1) > 35) {
            $shortBillStreet1 = substr($billing_street[0], 0, strrpos(substr($billing_street[0], 0, 35), ' '));
            $remainder    = str_replace($shortBillStreet1, "", $billing_street[0]);

            $billingLine1 = $shortBillStreet1;
            $billingLine2 = $remainder . ', ' . $billingLine2;
        }

        if (strlen($billingLine2) > 35) {
            $billingLine2a = $billingLine2;
            $shortBillStreet2 = substr($billingLine2, 0, strrpos(substr($billingLine2, 0, 35), ' '));
            $remainder    = str_replace($shortBillStreet1, "", $billingLine2a);

            $billingLine1 = $shortBillStreet2;
            $billingLine3 = $remainder;
        }

        // Get shipping address lines and split if required
        $shipping_street = $shipping_address->getStreet();
        $shippingLine1 = (isset($shipping_street[0])) ? $shipping_street[0] : '';
        $shippingLine2 = (isset($shipping_street[1])) ? $shipping_street[1] : '';
        $shippingLine3 = '';

        if (strlen($shippingLine1) > 35) {
            $shortStreet1 = substr($shipping_street[0], 0, strrpos(substr($shipping_street[0], 0, 35), ' '));
            $remainder    = str_replace($shortStreet1, "", $shipping_street[0]);

            $shippingLine1 = $shortStreet1;
            $shippingLine2 = $remainder . ', ' . $shippingLine2;
        }

        if (strlen($shippingLine2) > 35) {
            $shippingLine2a = $shippingLine2;
            $shortShipStreet2 = substr($shippingLine2, 0, strrpos(substr($shippingLine2, 0, 35), ' '));
            $remainder    = str_replace($shortShipStreet2, "", $shippingLine2a);

            $shippingLine2 = $shortShipStreet2;
            $shippingLine3 = $remainder;
        }

        if ($billing_address->getCompany() != '') {
            $billing_address_1 = str_replace("&", "and", $billing_address->getCompany());
            $billing_address_2 = str_replace("&", "and", $billingLine1);
            $billing_address_3 = str_replace("&", "and", $billingLine2);
        } else {
            $billing_address_1 = str_replace("&", "and", $billingLine1);
            $billing_address_2 = str_replace("&", "and", $billingLine2);
            $billing_address_3 = str_replace("&", "and", $billingLine3);
        }

        if ($shipping_address->getCompany() != '') {
            $shipping_address_1 = str_replace("&", "and", $shipping_address->getCompany());
            $shipping_address_2 = str_replace("&", "and", $shippingLine1);
            $shipping_address_3 = str_replace("&", "and", $shippingLine2);
        } else {
            $shipping_address_1 = str_replace("&", "and", $shippingLine1);
            $shipping_address_2 = str_replace("&", "and", $shippingLine2);
            $shipping_address_3 = str_replace("&", "and", $shippingLine3);
        }

        $this->logThis('Order ref: ' . $order->getId() . ' debug 1');

        // Get misc fields for order
        $brand     = $this->getBrandName($order->getStoreId());
        $invPrio   = '';
        $shipping  = $this->convertDeliveryCode($order);
        if ($shipping) {
            $shipping_method = $shipping[0];
            $invPrio         = $shipping[1];
        }

        $this->logThis('Order ref: ' . $order->getId() . ' debug 2');

        // Payment
        $payment     = $order->getPayment();
        $method      = $payment->getMethodInstance();
        $methodName  = $payment->getMethod();
        $methodTitle = $method->getTitle();
        $paymentDet  = $this->getPaymentInfo($order->getId());

        $bankAccount = '';
        $accNum      = '';
        if (strpos(strtolower($methodName), 'pal') !== false) {
            $bankAccount = 'PayPal Account';
            $accNum      = 2;
        }

        $this->logThis('Order ref: ' . $order->getId() . ' debug 3');
        // ***** TMP REMOVE - sales_source NEEDS THE VALUES SET IN ELUCID ***** TO DO //
        //$location = false;

        $xml = '
		<SALES_ORDERS xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.keystonesoftware.co.uk/xml/KSDXMLImportFormat.xsd">
			<SALES_ORDER>
				<CUSTOMER_DETAIL>
					<IS_NEW_CUSTOMER>0</IS_NEW_CUSTOMER>
					<COMPANY_CODE>' . $urn . '</COMPANY_CODE>
					<OTHER_REF>MET_' . $oRef . '</OTHER_REF>
					<WEB_USER>' . $email . '</WEB_USER>
					<COMPANY_CLASS>' . $kclass . '</COMPANY_CLASS>
					<COMPANY_TYPE></COMPANY_TYPE>
					<COMPANY_NAME>' . $fName . ' ' . $lName . '</COMPANY_NAME>
					<SOURCE_CODE>' . $howHeard . '</SOURCE_CODE>
					<MAILING_STATUS>' . $mailingStatus . '</MAILING_STATUS>
					<OPTIN_NEWSLETTER>' . $subsc . '</OPTIN_NEWSLETTER>
					<TAX_REFERENCE/>
					<ADDRESSES>
						<INVADDR>
							<IADDRESS1>' . $billing_address_1 . '</IADDRESS1>
							<IADDRESS2>' . $billing_address_2 . '</IADDRESS2>
							<IADDRESS3>' . $billing_address_3 . '</IADDRESS3>
							<ITOWN>' . $billing_address->getCity() . '</ITOWN>
							<ICOUNTY>' . $billing_address->getRegion() . '</ICOUNTY>
							<IPOSTCODE>' . $billing_address->getPostcode() . '</IPOSTCODE>
							<ICOUNTRY_CODE>' . $billing_address->getCountryId() . '</ICOUNTRY_CODE>
							<ICOUNTRY_NAME />
							<ITITLE>' . $order->getTitle() . '</ITITLE>
							<IFORENAME>' . $fName . '</IFORENAME>
							<ISURNAME>' . $lName . '</ISURNAME>
							<ITEL>' . $billing_address->getTelephone() . '</ITEL>
							<IFAX>' . $billing_address->getFax() . '</IFAX>
							<IMOBILE></IMOBILE>
							<IEMAIL>' . $email . '</IEMAIL>
							<IEMAIL_SUBSCRIBER>' . $subsc . '</IEMAIL_SUBSCRIBER>
							<IDOB/>
							<ORGANISATION/>
						</INVADDR>
						<DELADDR>
							<DADDRESS1>' . $shipping_address_1 . '</DADDRESS1>
							<DADDRESS2>' . $shipping_address_2 . '</DADDRESS2>
							<DADDRESS3>' . $shipping_address_3 . '</DADDRESS3>
							<DTOWN>' . $shipping_address->getCity() . '</DTOWN>
							<DCOUNTY>' . $shipping_address->getRegion() . '</DCOUNTY>
							<DPOSTCODE>' . $shipping_address->getPostcode() . '</DPOSTCODE>
							<DCOUNTRY_CODE>' . $shipping_address->getCountryId() . '</DCOUNTRY_CODE>
							<DCOUNTRY_NAME/>
							<DTITLE>' . $shipping_address->getTitle() . '</DTITLE>
							<DFORENAME>' . $shipping_address->getFirstname() . '</DFORENAME>
							<DSURNAME>' . $shipping_address->getLastname() . '</DSURNAME>
							<DTEL>' . $shipping_address->getTelephone() . '</DTEL>
							<DFAX>' . $shipping_address->getFax() . '</DFAX>
							<DMOBILE></DMOBILE>
							<DEMAIL>' . $shipping_address->getEmail() . '</DEMAIL>
							<DEMAIL_SUBSCRIBER>' . $subsc . '</DEMAIL_SUBSCRIBER>
							<DDOB/>
							<ORGANISATION/>
						</DELADDR>
					</ADDRESSES>
				</CUSTOMER_DETAIL>
				<PAYMENTS>
					<PAYMENT_DETAIL>
						<PAYMENT_AMOUNT>' . $order->getGrandTotal() . '</PAYMENT_AMOUNT>
						<PAYMENT_TYPE>2</PAYMENT_TYPE>
						<CARD_TYPE></CARD_TYPE>
						<CARD_NUMBER></CARD_NUMBER>
						<CARD_START></CARD_START>
						<CARD_EXPIRE></CARD_EXPIRE>
						<CARD_ISSUE></CARD_ISSUE>
						<CARD_CV2></CARD_CV2>
						<PREAUTH>0</PREAUTH>
						<AUTH_CODE>' . $paymentDet['auth'] . '</AUTH_CODE>
						<TRANSACTION_ID>' . $paymentDet['vpstxid'] . '</TRANSACTION_ID>
						<PREAUTH_REF>' . $paymentDet['vendortx'] . '</PREAUTH_REF>
						<SECURITY_REF>' . $paymentDet['security'] . '</SECURITY_REF>
						<SECURITY_COMMENT></SECURITY_COMMENT>
						<ACCOUNT_NUMBER>' . $accNum . '</ACCOUNT_NUMBER>
						<ACCOUNT_NAME>' . $bankAccount . '</ACCOUNT_NAME>
					</PAYMENT_DETAIL>
				</PAYMENTS>
				<ORDER_HEADER>
					<ORDER_DATE>' . $created . '</ORDER_DATE>
					<DELIVERY_DATE></DELIVERY_DATE>
					<ORDER_AMOUNT>' . $order->getGrandTotal() . '</ORDER_AMOUNT>
					<ORDER_CURRENCY_CODE>' . $order->getOrderCurrencyCode() . '</ORDER_CURRENCY_CODE>
					<SITE>' . $brand . '</SITE>
					<ASSOCIATED_REF>MET_' . $order->getId() . '___TEST</ASSOCIATED_REF>
					<AGENT>' . $salesRep . '</AGENT>
					<BRAND>' . $brand . '</BRAND>
					<SALES_SOURCE>' . $location . '</SALES_SOURCE>
					<ORDER_NOTE>' . $orderNote . '</ORDER_NOTE>
					<INVOICE_NOTE/>
					<DELIVERY_NET>' . $order->getShippingAmount() . '</DELIVERY_NET>
					<DELIVERY_TAX>' . $order->getShippingTaxAmount() . '</DELIVERY_TAX>
					<DELIVERY_GRS>' . $del_gross . '</DELIVERY_GRS>
					<COURIER_CODE/>
					<COURIER_DESC>' . $shipping_method . '</COURIER_DESC>
					<PO_NUMBER></PO_NUMBER>
					<KEYCODE_CODE/>
					<COURIER_NOTE></COURIER_NOTE>
					<INV_PRIORITY>' . $invPrio . '</INV_PRIORITY>
					<GIFT_AID>0</GIFT_AID>
					<MANUAL_RECEIVED>0</MANUAL_RECEIVED>
					<REQUIRED_BY_DATE></REQUIRED_BY_DATE>
					<WEBSITE_NAME>' . $brand . '</WEBSITE_NAME>
				</ORDER_HEADER>
				<ORDER_ITEMS>';

        // get the order items
        $items = $order->getAllVisibleItems();

        // need to loop through the order items
        foreach ($items as $product_item) {
            $vatDivisor   = 1 + ($product_item->getTaxPercent() / 100);
            $discountNet  = $product_item->getDiscountAmount();
            $discountGrs  = round($product_item->getDiscountAmount()*$vatDivisor, 2);
            $netSingle    = ($product_item->getRowTotal() - $discountNet) / $product_item->getQtyOrdered();
            $grsSingle    = ($product_item->getRowTotalInclTax()-$discountGrs) / $product_item->getQtyOrdered();

            $netSingleWD  = round($grsSingle/$vatDivisor, 2);

            $xml .= '<ORDER_ITEM>
						<STOCK_CODE>' . $product_item->getSku() . '</STOCK_CODE>
						<MAPPING_TYPE>1</MAPPING_TYPE>
						<STOCK_DESC>' . $this->_escaper->escapeHtml($product_item->getName()) . '</STOCK_DESC>
						<ORDER_QTY>' . number_format($product_item->getQtyOrdered()) . '</ORDER_QTY>
						<PRICE_NET>' . $netSingleWD . '</PRICE_NET>
						<PRICE_GRS>' . $grsSingle . '</PRICE_GRS>
						<TAX_RATE>1</TAX_RATE>
						<FREEITEM_REASON/>
						<IMPORT_REF/>
					</ORDER_ITEM>';
        }
        $xml .= '
				</ORDER_ITEMS>
			</SALES_ORDER>
			<SALES_ORDER/>
		</SALES_ORDERS>
		';

        // TMP
        $this->logThis("5. addNewOrderToElucid, XML built: " . $order->getId());
        //$xml = str_replace("&","and",$xml);
        $this->logThis($xml);

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            //$client = $soapClient = $this->soapClientFactory->create($wsdl, array(
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        try {
            $result = $client->ImportOrders($xml);
        } catch (SoapFault $exception) {
            // need to write this error to a database
            $this->logThis($order->getIncrementId() . ' EXCEPTION');
            $fault = $exception->faultstring;
            $this->logThis($order->getIncrementId() . ' : exception: ' . $fault . ' XML is: ' . $xml);

            if ($kOrder) {
                $notes = $kOrder->getNotes();
                $date  = date("d-m-Y H:i:s");
                $kOrder->setNotes($date . ': exception: ' . $exception->faultstring . ' XML is: ' . $xml . "\r\n" . $notes);
                $kOrder->setIntegrationStatus("error");
                $kOrder->save();
            }

            return false;
        }

        if (isset($result->ImportedCount)) {
            if ($result->ImportedCount == 0) {
                $elucid_result = print_r($result, true);

                if ($kOrder) {
                    $notes = $kOrder->getNotes();
                    $date  = date("d-m-Y H:i:s");
                    $kOrder->setNotes($date . ': exception2: ' . $elucid_result . "\r\n" . $notes);
                    $kOrder->setIntegrationStatus("error");
                    $kOrder->save();
                }

                $this->logThis($order->getIncrementId() . ' : exception2 ' . $elucid_result);
                return false;
            } else {
                // write success $result->OrderImport[0]->SalesOrderCode to db
                $elucid_soc = $result->OrderImport[0]->SalesOrderCode;

                if ($kOrder) {
                    $notes = $kOrder->getNotes();
                    $date  = date("d-m-Y H:i:s");
                    $kOrder->setNotes($date . ': Imported: ' . $elucid_soc . "\r\n" . $notes);
                    $kOrder->setIntegrationStatus("imported");
                    $kOrder->setElucidOrdernumber($elucid_soc);
                    $kOrder->setElucidLocation($location);
                    $kOrder->save();

                    if ($newState) {
                        $order->addStatusToHistory($order->getStatus(), 'Update to: ' . $newState);
                        $order->setState('processing')->setStatus($newState);
                    }
                    $order->setElucidSoc($elucid_soc)->save();
                }

                $this->logThis($order->getIncrementId() . ': Imported: ' . $elucid_soc);
                return false;
            }
        }

        return true;
    }

    public function createElucidCustomer($user, $billing_address, $wsdl, $kOrder=false, $salesRep = false, $subsc, $force=false, $howHeard, $mailingStatus)
    {
        $profession    = '';
        $governingbody = '';

        if ($user) {
            $email = $user->getEmail();
            $fName = $user->getFirstname();
            $lName = $user->getLastname();
            $oRef  = $user->getId();

            // Check for new practitioner attributes
            $prof  = $user->getProfession();
            if ($prof) {
                $attr = $user->getResource()->getAttribute('profession');
                if ($attr->usesSource()) {
                    $profession = $attr->getSource()->getOptionText($prof);
                }
            }

            $govbo = $user->getGoverningBody();
            if ($govbo) {
                $attr = $user->getResource()->getAttribute('governing_body');
                if ($attr->usesSource()) {
                    $governingbody = $attr->getSource()->getOptionText($govbo);
                }
            }
        } else {
            if ($billing_address) {
                $email = $billing_address->getEmail();
                $fName = $billing_address->getFirstname();
                $lName = $billing_address->getLastname();
                $oRef  = 'GUEST';
            }
        }

        if (!$email) {
            return false;
        }

        $companyClass = $this->getDefaultCustomerClass();

        $billing_street  = $billing_address->getStreet();
        $billingLine1 = (isset($billing_street[0])) ? $billing_street[0] : '';
        $billingLine2 = (isset($billing_street[1])) ? $billing_street[1] : '';
        $billingLine3 = '';

        if (strlen($billingLine1) > 35) {
            $shortBillStreet1 = substr($billing_street[0], 0, strrpos(substr($billing_street[0], 0, 35), ' '));
            $remainder    = str_replace($shortBillStreet1, "", $billing_street[0]);

            $billingLine1 = $shortBillStreet1;
            $billingLine2 = $remainder . ', ' . $billingLine2;
        }

        if (strlen($billingLine2) > 35) {
            $billingLine2a = $billingLine2;
            $shortBillStreet2 = substr($billingLine2, 0, strrpos(substr($billingLine2, 0, 35), ' '));
            $remainder    = str_replace($shortBillStreet1, "", $billingLine2a);

            $billingLine1 = $shortBillStreet2;
            $billingLine3 = $remainder;
        }

        if ($billing_address->getCompany() != '') {
            $billing_address_1 = str_replace("&", "and", $billing_address->getCompany());
            $billing_address_2 = str_replace("&", "and", $billingLine1);
            $billing_address_3 = str_replace("&", "and", $billingLine2);
        } else {
            $billing_address_1 = str_replace("&", "and", $billingLine1);
            $billing_address_2 = str_replace("&", "and", $billingLine2);
            $billing_address_3 = str_replace("&", "and", $billingLine3);
        }

        if (!$salesRep) {
            $salesRep = '';
        }

        $xml = '<?xml version="1.0"?>
				<COMPANYS>
					<COMPANY>
					    <IS_NEW_CUSTOMER>-1</IS_NEW_CUSTOMER>
						<COMPANY_CODE></COMPANY_CODE>
						<COMPANY_NAME>' . $fName . ' ' . $lName . '</COMPANY_NAME>
						<OTHER_REF>MET_' . $oRef . '</OTHER_REF>
						<COMPANY_CLASS>' . $companyClass . '</COMPANY_CLASS>
						<COMPANY_TYPE />
						<COMPANY_STATUS>Active</COMPANY_STATUS>
						<COMPANY_SOURCE />';

        if ($salesRep && strlen($salesRep)> 2) {
            $xml .= '<AGENT_NAME>' . $salesRep . '</AGENT_NAME>
				';
        }
        $xml .=	'<SOURCE_CODE>' . $howHeard . '</SOURCE_CODE>
						<DATE_CREATED />
						<COMPANY_ID />
						<PROFORMA>0</PROFORMA>
						<SORDER_LOCKED />
						<SUPPLIER />
						<EC_COMPANY />
						<PAYS_VAT />
						<POCODE_REQURED />
						<WEB_USER>' . $email . '</WEB_USER>
						<WEB_PASSWORD />
						<TAX_REFERENCE />
						<MAILING_STATUS>' . $mailingStatus . '</MAILING_STATUS>
						<CUSTOMER_DISCOUNT />
						<SALE_SOURCE>UK</SALE_SOURCE>
						<CURRENCY_CODE />
						<ADDRESSES>
							<ADDRESS>
								 <ADDR1>' . $billing_address_1 . '</ADDR1>
								 <ADDR2>' . $billing_address_2 . '</ADDR2>
								 <ADDR3>' . $billing_address_3 . '</ADDR3>
								 <TOWN>' . $billing_address->getCity() . '</TOWN>
								 <COUNTY>' . $billing_address->getRegion() . '</COUNTY>
								 <POSTCODE>' . $billing_address->getPostcode() . '</POSTCODE>
								 <ORGANISATION />
								 <ADDRTYPE />
								 <EMAIL>' . $email . '</EMAIL>
								 <TEL>' . $billing_address->getTelephone() . '</TEL>
								 <FAX>' . $billing_address->getFax() . '</FAX>
								 <COUNTRY/>
								 <COUNTRY_CODE>' . $billing_address->getCountryId() . '</COUNTRY_CODE>
								 <ADDRESS_ID>' . $billing_address->getEntityId() . '</ADDRESS_ID>
								 <INACTIVE>0</INACTIVE>
								 <CONTACTS>
								 	<CONTACT>
								 		<TITLE />
								 		<FORENAME>' . $fName . '</FORENAME>
								 		<SURNAME>' . $lName . '</SURNAME>
								 		<JOBTITLE />
								 		<TEL>' . $billing_address->getTelephone() . '</TEL>
								 		<FAX>' . $billing_address->getFax() . '</FAX>
								 		<MOBILE />
								 		<EMAIL>' . $email . '</EMAIL>
								 		<NOTE />
								 		<EMAILSUBSCRIBE>' . $subsc . '</EMAILSUBSCRIBE>
										';
        if ($subsc < 0) {
            $xml .=	'<MAILING_FLAG>2</MAILING_FLAG>
										';
        }
        $xml .=	'<DOB />
								 		<CONTACT_ID />
								 		<INACTIVE>0></INACTIVE>
								 	</CONTACT>
								 </CONTACTS>
							</ADDRESS>
						</ADDRESSES>
						<COUNTRY>' . $billing_address->getCountryId() . '</COUNTRY>';
        if (strlen($profession)>1) {
            $xml .= '<ADDITIONAL NAME="Profession 1">' . $profession . '</ADDITIONAL>';
        }
        if (strlen($governingbody)>1) {
            $xml .= '<ADDITIONAL NAME="Association 1">' . $governingbody . '</ADDITIONAL>';
        }

        $xml .= '</COMPANY>
				</COMPANYS>
			';

        //$xml = str_replace("&","and",$xml);
        // TMP for bug
        $this->logThis("Creating customer with the following XML");
        $this->logThis($xml);

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        try {
            if ($force) {
                $mode = 3;
            } else {
                $mode = 0;
            }
            $result = $client->ImportCompany($xml, $mode);
            $urn = $result->CustomerImport[0]->URN;
        } catch (SoapFault $exception) {
            if ($kOrder) {
                $notes = $kOrder->getNotes();
                $date   = date("d-m-Y H:i:s");
                $kOrder->setNotes($date . ': Couldnt create customer: exception' . $exception->faultstring . "\r\n" . $notes);
                $kOrder->setIntegrationStatus("customer-error");
                $kOrder->save();
            }

            $this->logThis($kOrder->getMagentoId() . ': Couldnt create customer: exception' . $exception->faultstring);

            return false;
        }

        $this->logThis("After create customer");

        return $urn;
    }

    public function updateElucidCustomer($user, $subsc)
    {

        // This doesnt work - TO DO - can we update all subscriptions for the custom?
        $wsdl  = $this->getElucidWsdl();
        $email = $user->getEmail();
        $fName = $user->getFirstname();
        $lName = $user->getLastname();
        $oRef  = $user->getId();

        if (!$email) {
            return false;
        }

        $xml = '<?xml version="1.0"?>
				<companys>
					<company>
						<company_code></company_code>
						<company_name>' . $fName . ' ' . $lName . '</company_name>
						<other_ref>MET_' . $oRef . '</other_ref>
						<company_type />
						<company_status>Active</company_status>
						<company_source />
						<web_user>' . $email . '</web_user>
						<web_password />
						<tax_reference />
						<mailing_status>3</mailing_status>
						<customer_discount />
						<sale_source>UK</sale_source>
						<currency_code />
						<addresses>
							<ADDRESS>
								 <contacts>
								 	<contact>
								 		<emailsubscribe>' . $subsc . '</emailsubscribe>
								 	</contact>
								 </contacts>
							</ADDRESS>
						</addresses>
					</company>
				</companys>
			';

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        try {
            $result = $client->ImportCompany($xml, 0);
            $urn = $result->CustomerImport[0]->URN;
        } catch (SoapFault $exception) {
            if ($kOrder) {
                $notes = $kOrder->getNotes();
                $date   = date("d-m-Y H:i:s");
                $kOrder->setNotes($date . ': Couldnt update customer: exception' . $exception->faultstring . "\r\n" . $notes);
                $kOrder->setIntegrationStatus("customer-error");
                $kOrder->save();
            }

            $this->logThis('Couldnt update customer: exception' . $exception->faultstring);

            return false;
        }

        return $urn;
    }

    public function queryElucidCustomer($user, $wsdl, $kOrder=false, $billing_address=false)
    {
        if ($user) {
            $email        = $user->getEmail();
            $fName        = $user->getFirstname();
            $lName        = $user->getLastname();
        } else {
            if ($billing_address) {
                $email        = $billing_address->getEmail();
                $fName        = $billing_address->getFirstname();
                $lName        = $billing_address->getLastname();
            }
        }

        $elucidclass = false;
        $elucidurn   = false;

        if (!$email) {
            return false;
        }

        try {
            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $client = new \SoapClient($wsdl, [
                'trace' => 1,
                'stream_context' => $streamContext
            ]);

            $result = $client->ExportCompany($fName . ' ' . $lName, 4);
            if ($result) {
                $xml = new \SimpleXMLElement($result);
                $i=0;
                if ($xml) {
                    foreach ($xml->COMPANY as $company) {
                        if ($xml->COMPANY[$i]->WEB_USER == $email) {
                            $elucidurn   = $xml->COMPANY[$i]->COMPANY_CODE;
                            $elucidclass = $xml->COMPANY[$i]->COMPANY_CLASS;
                        }

                        if ($elucidurn == '') {
                            foreach ($xml->COMPANY[$i]->ADDRESSES->ADDRESS as $address) {
                                if ($address->EMAIL == $email) {
                                    $elucidurn = $xml->COMPANY[$i]->COMPANY_CODE;
                                    if (!$elucidclass || $elucidclass == '') {
                                        $elucidclass = $xml->COMPANY[$i]->COMPANY_CLASS;
                                    }
                                }
                            }
                        }

                        $i++;
                    }
                }
            }
        } catch (SoapFault $exception) {
            // need to write this error to a database
            if ($kOrder) {
                $notes = $kOrder->getNotes();
                $date   = date("d-m-Y H:i:s");
                $kOrder->setNotes($date . ': Error querying customer exception' . $exception->faultstring . "\r\n" . $notes);
                $kOrder->setIntegrationStatus("error");
                $kOrder->save();
            }

            $this->logThis($order->getIncrementId() . ' : Error querying customer exception' . $exception->faultstring);

            return false;
        }

        if ($elucidclass && $user) {
            $this->updateCustomerClass($elucidclass, $user);
        }

        return $elucidurn;
    }

    public function getElucidCustomer($user, $kOrder=false, $billing_address=false)
    {
        $wsdl  = $this->getElucidWsdl();

        if ($user) {
            $email        = $user->getEmail();
            $fName        = $user->getFirstname();
            $lName        = $user->getLastname();
        } else {
            if ($billing_address) {
                $email        = $billing_address->getEmail();
                $fName        = $billing_address->getFirstname();
                $lName        = $billing_address->getLastname();
            }
        }

        $elucidclass = false;
        $elucidurn   = false;

        if (!$email) {
            return false;
        }

        try {
            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $client = new \SoapClient($wsdl, [
                'trace' => 1,
                'stream_context' => $streamContext
            ]);

            $result = $client->ExportCompany($fName . ' ' . $lName, 4);
            if ($result) {
                return $result;
            }
        } catch (SoapFault $exception) {
            $this->logThis($order->getIncrementId() . ' : Error querying customer exception' . $exception->faultstring);

            return false;
        }

        return $elucidurn;
    }

    public function getElucidClass($user, $wsdl, $kOrder=false, $billing_address=false)
    {
        if ($user) {
            $email        = $user->getEmail();
            $fName        = $user->getFirstname();
            $lName        = $user->getLastname();
        } else {
            if ($billing_address) {
                $email        = $billing_address->getEmail();
                $fName        = $billing_address->getFirstname();
                $lName        = $billing_address->getLastname();
            }
        }

        $elucidclass = false;
        $elucidurn   = false;

        if (!$email) {
            return false;
        }

        try {
            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);

            $client = new \SoapClient($wsdl, [
                'trace' => 1,
                'stream_context' => $streamContext
            ]);

            $result = $client->ExportCompany($fName . ' ' . $lName, 4);
            if ($result) {
                $xml = new \SimpleXMLElement($result);
                $i=0;
                if ($xml) {
                    foreach ($xml->COMPANY as $company) {
                        if ($xml->COMPANY[$i]->WEB_USER == $email) {
                            $elucidurn   = $xml->COMPANY[$i]->COMPANY_CODE;
                            $elucidclass = $xml->COMPANY[$i]->COMPANY_CLASS;
                        }

                        if ($elucidclass == '') {
                            foreach ($xml->COMPANY[$i]->ADDRESSES->ADDRESS as $address) {
                                if ($address->EMAIL == $email) {
                                    $elucidclass = $xml->COMPANY[$i]->COMPANY_CLASS;
                                }
                            }
                        }

                        $i++;
                    }
                }
            }
        } catch (SoapFault $exception) {
            // need to write this error to a database
            if ($kOrder) {
                $notes = $kOrder->getNotes();
                $kOrder->setNotes('Error querying customer class exception' . $exception->faultstring . "\r\n" . $notes);
                $kOrder->setIntegrationStatus("error");
                $kOrder->save();
            }

            $this->logThis($order->getIncrementId() . ' : Error querying elucid class exception' . $exception->faultstring);

            return false;
        }

        return $elucidclass;
    }

    public function updateCustomerClass($elucidclass, $customer)
    {
        $currentGid   = $customer->getGroupId();
        $groupFactory = $this->elucidCustomergroupFactory->create()->addFieldToFilter('khoas_customer_class', $elucidclass);
        if ($groupFactory->getSize()) {
            $newId = $groupFactory->getFirstItem()->getMagentoCustomerGroup();
            if ($newId !== $currentGid) {
                $customer->setGroupId($newId)->save();
            }
        }
    }

    public function logThis($msg)
    {

        $this->logger->critical('---> Elucid', ['message', $msg]);

        /*
        $date   = date("d-m-Y H:i:s");
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/elucid.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($date . ': ' . $msg);
        */
    }

    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function resolveSalesrep($couponcode)
    {
        $agent = $this->elucidAgentFactory->create()->addFieldToFilter('discount_code', $couponcode);
        if ($agent->getSize()) {
            return $agent->getFirstItem()->getAgentName();
        }
        return false;
    }

    public function getBrandName($sid)
    {
        $brandFactory = $this->elucidBrandFactory->create()->addFieldToFilter('store_id', $sid);
        if ($brandFactory->getSize()) {
            return $brandFactory->getFirstItem()->getElucidBrand();
        }
        return false;
    }

    public function checkSubscribed($email)
    {
        //$checkSubscriber = $this->subscriber->loadByEmail($email);

        //if ($checkSubscriber->isSubscribed()) {
        //	return true;
        //}

        //return false;
        $email = trim($email);
        $connection   = $this->resource->getConnection();
        $emailTable   = $this->resource->getTableName('newsletter_subscriber');
        $query        = "SELECT subscriber_email FROM $emailTable WHERE subscriber_email = '$email' AND subscriber_status = 1";

        $this->logThis($query);

        $emailSub     = $connection->fetchAll($query);

        if ($emailSub) {
            return true;
        }
        return false;
    }

    public function getSourceCode($order)
    {
        $howHeard = $order->custom('how_heard');
        $othHeard = $order->custom('other_heard');
        if ($howHeard || $othHeard) {
        }
    }

    public function getDeliveryNote($order)
    {
        $attributes = $this->getAmastyAttributes($order->getId());
        if (!$attributes) {
            return false;
        }

        if (isset($attributes['delivery_notes'])) {
            return $attributes['delivery_notes'];
        }

        return false;
    }

    public function getHowHeard($order)
    {
        $attributes = $this->getAmastyAttributes($order->getId());
        if (!$attributes) {
            return false;
        }

        if (isset($attributes['how_heard'])) {
            return $attributes['how_heard'];
        }

        return false;
    }

    public function getOtherHeard($order)
    {
        $attributes = $this->getAmastyAttributes($order->getId());
        if (!$attributes) {
            return false;
        }

        if (isset($attributes['other_heard'])) {
            return $attributes['other_heard'];
        }

        return false;
    }

    public function getAmastyAttributes($oid)
    {

        // Set store to admin for the codes we need
        $this->_storeManager->setCurrentStore(0);

        $apiOrder = $this->apiOrder->load($oid);
        $this->amAdapter->addExtensionAttributesToOrder($apiOrder);
        $extA = $apiOrder->getExtensionAttributes();
        if (!$extA) {
            return false;
        }
        $exts = $extA->getAmastyOrderAttributes();
        if (!$exts) {
            return false;
        }

        $entRe = $this->amResolver;
        $entity = $entRe->getEntityByOrder($apiOrder);
        $mForm = $this->amForm;
        //$form = $mForm->create()->setFormCode('adminhtml_order_view')->setEntity($entity)->setStore($apiOrder->getStoreId());
        $form = $mForm->create()->setFormCode('adminhtml_order_view')->setEntity($entity);
        $outputData = $form->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);

        $attrs = [];
        foreach ($outputData as $attributeCode => $data) {
            if (!empty($data)) {
                $attrs[$attributeCode] = $data;
            }
        }

        if (count($attrs) > 0) {
            return $attrs;
        }
        return false;
    }

    public function convertDeliveryCode($order)
    {
        $shipping_code = $order->getShippingMethod();
        if (!$shipping_code) {
            return false;
        }

        $shipping = $this->elucidShippingFactory->create()->addFieldToFilter('magento_shipping', $shipping_code);
        if ($shipping->getSize()) {
            $elucidShip  =  $shipping->getFirstItem()->getElucidShipping();
            $invPrio    =  $shipping->getFirstItem()->getInvoicePrio();
            return [$elucidShip,$invPrio];
        }
        return false;
    }

    public function getStateByLocation($location)
    {
        $status = $this->elucidStatusFactory->create()->addFieldToFilter('elucid_location', $location);
        if ($status->getSize()) {
            return $status->getFirstItem()->getMagentoStatus();
        }
    }

    public function getAgentList()
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        $result = $client->getAgentList();
        if ($result) {
            return $result;
        }
    }

    public function getCompanyClassList()
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        $result = $client->getCompanyClass();
        if ($result) {
            return $result;
        }
    }

    public function getOrderUpdate($khord)
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        $result = $client->ExportOrders($khord, 1);
        if ($result) {
            return $result;
        }
    }

    public function getOrderUpdateStatus($khord)
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        $result = $client->ExportOrderStatusEx($khord, 3);
        if ($result) {
            return $result;
        }
    }

    public function getOrderUpdateStatusField($khord)
    {
        $disabled = $this->isElucidDisabled();
        if ($disabled) {
            return true;
        }

        $wsdl = $this->getElucidWsdl();
        if (!$wsdl) {
            return true;
        }

        $streamContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $client = new \SoapClient($wsdl, [
            'trace' => 1,
            'stream_context' => $streamContext
        ]);

        $result = $client->ExportOrderStatusEx($khord, 3);
        if ($result) {
            $xml2 = \simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml2);
            $array = json_decode($json, true);

            // TO DO - WHAT IF THERE ARE MORE INVOICES HERE?
            //return $array['ORDER_STATUS']['INVOICES']['INVOICE']['@attributes']['INVOICE_STAGE_DESC'];

            // MAYBE THIS WILL GET THE LAST VALUE?
            $stage_desc = '';

            if (is_array($array) && isset($array['ORDER_STATUS']['INVOICES'])) {
                foreach ($array['ORDER_STATUS']['INVOICES'] as $invoice) {
                    $stage_desc = $invoice['@attributes']['INVOICE_STAGE_DESC'];
                }
            }
            return $stage_desc;
        }
    }

    public function getOrderQueueUpdate()
    {

        // TO DO - MADE ASSUMPTIONS HERE ABOUT THE CONTENT OF THE INVOICE STAGE DESCRIPTION

        $date         = date("Y-m-d H:i:s", strtotime("-2 week"));
        $connection   = $this->resource->getConnection();
        $salesTable   = $this->resource->getTableName('sales_order');
        $query        = "SELECT * FROM $salesTable WHERE created_at > '$date' AND state = 'processing' AND elucid_soc IS NOT NULL";
        $orders       = $connection->fetchAll($query);

        $staging  = $this->getStateByLocation('staging');
        $picking  = $this->getStateByLocation('picking');
        $complete = $this->getStateByLocation('issued');

        foreach ($orders as $order) {

            //echo 'Working on: '.$order['elucid_soc']." \r\n";
            $checkInQueue = $this->checkIsInQueue($order);
            if (!$checkInQueue) {
                continue;
            }
            $this->logThis("Checking order update for: " . $order['increment_id']);
            $currentStatus  = $order['status'];
            $elucidInvStatus = $this->getOrderUpdateStatusField($order['elucid_soc']);

            if ($elucidInvStatus) {
                $this->logThis("Checking order update for: " . $order['increment_id'] . ' got status: ' . $elucidInvStatus);
                // STAGING
                if (strpos(strtoupper($elucidInvStatus), 'STAGING') !== false) {
                    if ($currentStatus == $staging) {
                        continue;
                    }
                    $this->updateOrderStatus($order['increment_id'], $staging);
                }

                // PICKING
                if (strpos(strtoupper($elucidInvStatus), 'PICKING') !== false) {
                    if ($currentStatus == $picking) {
                        continue;
                    }
                    $this->updateOrderStatus($order['increment_id'], $picking);
                }

                // ISSUED
                if (strpos(strtoupper($elucidInvStatus), 'ISSUED') !== false) {
                    if ($currentStatus == $complete) {
                        continue;
                    }
                    $this->updateOrderStatus($order['increment_id'], $complete, true);
                }
            }
        }

        return true;
    }

    public function checkIsInQueue($order)
    {
        $connection   = $this->resource->getConnection();
        $kOTable      = $this->resource->getTableName('pixiemedia_elucid_order');
        $incId        = $order['increment_id'];
        $query        = "SELECT elucid_ordernumber FROM $kOTable WHERE magento_id = '$incId'";
        $result       = $connection->fetchOne($query);

        if ($result) {
            return true;
        }

        return false;
    }

    public function updateOrderStatus($oid, $status, $complete=false)
    {

        //echo 'UPDATING! '.$oid.' '.$status.' '.$complete;

        $order  = $this->_orderFactory->create()->loadByIncrementId($oid);
        if (!$complete) {
            $order->addStatusToHistory($order->getStatus(), 'updateOrderStatus function changed to: ' . $status);

            // Belt and braces to force the change
            $this->setStatusInTable($order->getId(), $status);

            $order->setState('processing')->setStatus($status)->save();
        } else {
            $order->addStatusToHistory($order->getStatus(), 'updateOrderStatus function changed to: ' . $status);
            $order->setState('complete')->setStatus($status)->save();
            $this->shipOrder($order);
        }

        return true;
    }

    public function shipOrder($order)
    {
        $convertOrder = $this->converter;
        $shipment = $convertOrder->toShipment($order);

        // Loop through order items
        $shipped = 0;
        foreach ($order->getAllItems() as $orderItem) {

            // Check if order item has qty to ship or is virtual
            if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();
            $shipped = $shipped+$qtyShipped;
            // Create shipment item with qty
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            // Add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        if ($shipped < 1) {
            return true;
        }

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();
            // Send email
            $this->shipNotifier
                ->notify($shipment);
            $shipment->save();
        } catch (Exception $e) {
            $this->logThis('Cant ship order ' . $order->getIncrementId() . ': ' . $e->getMessage());
        }
    }

    public function setStatusInTable($oid, $status)
    {
        if (!$oid || !$status) {
            return false;
        }

        $connection   = $this->resource->getConnection();
        $ordTable     = $this->resource->getTableName('sales_order');
        $query        = "UPDATE $ordTable SET status = '$status' WHERE entity_id = $oid";

        $connection->query($query);
        return true;
    }

    public function getPaymentInfo($oid)
    {
        $this->logThis("order ref: $oid debug 2.5 in getpaymentinfo");
        $connection   = $this->resource->getConnection();
        $payTable     = $this->resource->getTableName('sales_order_payment');
        $query        = "SELECT * FROM $payTable WHERE parent_id = $oid";

        $payment = $connection->fetchAll($query);

        if ($payment) {
            $this->logThis("order ref: $oid debug 2.6 in getpaymentinfo");
            $pay = $payment[0];
            $vpsTxId = (isset($pay['last_trans_id'])) ? $pay['last_trans_id'] : '';
            $additional = json_decode($pay['additional_information'], true);
            $authCode = (isset($additional['txAuthNo'])) ? $additional['txAuthNo'] : '';
            $vendorTx = (isset($additional['vendorTxCode'])) ? $additional['vendorTxCode'] : '';
            $securityKey = (isset($additional['securityKey'])) ? $additional['securityKey'] : '';

            // PayPal override
            if (strlen($authCode) < 2) {
                $authCode = (isset($additional['paypal_correlation_id'])) ? $additional['paypal_correlation_id'] : '';
            }

            $this->logThis("order ref: $oid debug 2.7 in getpaymentinfo");
            return ['vpstxid' => $vpsTxId,'auth'=>$authCode,'vendortx'=>$vendorTx,'security'=>$securityKey];
        } else {
            return ['vpstxid' => '','auth' => '','vendortx' => '','security' => ''];
        }

        return false;
    }

    public function fetchUserSubscriptions()
    {
        $connection   = $this->resource->getConnection();
        $eavTable     = $this->resource->getTableName('eav_attribute');

        // Fetch elucid_urn ID
        $elucidId      = $connection->fetchOne("SELECT attribute_id FROM $eavTable WHERE attribute_code = 'elucid_urn'");

        if (!$elucidId) {
            return false;
        }

        // Fetch all the unique records
        $varcharTable   = $this->resource->getTableName('customer_entity_varchar');
        $elucidCustomers = $connection->fetchAll("SELECT * FROM $varcharTable WHERE attribute_id = $elucidId");

        if (!$elucidCustomers) {
            return false;
        }

        $csv = [];
        $csv[] = ['email','elucid_urn','is_subscribed'];

        // Loop the customers
        foreach ($elucidCustomers as $sqlCustomer) {
            $user = $this->customer->load($sqlCustomer['entity_id']);
            if (!$user) {
                continue;
            }

            $subscribed = $this->checkSubscribed($user->getEmail());
            if ($subscribed) {
                $subsc = 'yes';
            } else {
                $subsc = 'no';
            }

            $csv[] = [$user->getEmail(),$sqlCustomer['value'],$subsc];
        }

        return $csv;
    }

    public function saveToCsv($csvData)
    {
        $log = $this->_dir->getPath('var') . '/log/';
        $fp = fopen($log . 'subscribers.csv', 'w');
        foreach ($csvData as $fields) {
            if (is_array($fields)) {
                fputcsv($fp, $fields);
            }
        }

        fclose($fp);

        $filePath = $log . 'subscribers.csv';
        return $filePath;
    }

    public function sendCsvMail()
    {
        $body = 'Please find attached your daily update on customers in Magento with a Elucid URN and their current subscription status.


	  Pixie Elucid Team';
        $subject = 'Pixie Elucid - Subcriber list';

        $clientEmail = $this->getSubscribeEmail();
        if (!$clientEmail) {
            $clientEmail = 'tom.dollar@pixiemedia.co.uk';
        }

        // This will provide plenty adequate entropy
        $multipartSep = '-----' . md5(time()) . '-----';

        // Arrays are much more readable
        $headers = [
            "From: Pixie Elucid <comms@nutristrength.com>",
            "Reply-To: comms@nutristrength.com",
            "Cc: tom@meltingpotdesign.co.uk",
            "Bcc: tom.dollar@pixiemedia.co.uk",
            "Content-Type: multipart/mixed; boundary=\"$multipartSep\""
        ];

        $csv = $this->fetchUserSubscriptions();
        $csvFile = $this->saveToCsv($csv);

        $cr = "\n";
        $csvData = '';
        foreach ($csv as $csvLine) {
            $csvData .= $csvLine[0] . ',' . $csvLine[1] . ',' . $csvLine[2] . $cr;
        }

        //echo $csvData;

        $attachment = chunk_split(base64_encode($csvData));

        // Make the body of the message
        $body = "--$multipartSep\r\n"
            . "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\r\n"
            . "Content-Transfer-Encoding: 7bit\r\n"
            . "\r\n"
            . "$body\r\n"
            . "--$multipartSep\r\n"
            . "Content-Type: text/csv\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "Content-Disposition: attachment; filename=\"subscribers.csv\"\r\n"
            . "\r\n"
            . "$attachment\r\n"
            . "--$multipartSep--";

        // Send the email, return the result
        return @mail($clientEmail, $subject, $body, implode("\r\n", $headers));
    }
    public function getIsAgent($couponCode)
    {
        $couponCode   = str_replace("'", "\'", $couponCode);
        $connection   = $this->resource->getConnection();
        $agentTable   = $this->resource->getTableName('pixiemedia_elucid_agentmap');
        $query        = "SELECT agent_name FROM $agentTable WHERE discount_code = '$couponCode'";
        return $connection->fetchOne($query);
    }

    public function getCouponExists($couponCode)
    {
        $couponCode   = str_replace("'", "\'", $couponCode);
        $connection   = $this->resource->getConnection();
        $couponTable  = $this->resource->getTableName('salesrule_coupon');
        $query        = "SELECT coupon_id FROM $couponTable WHERE code = '$couponCode'";
        return $connection->fetchOne($query);
    }

    public function getGroupIds()
    {
        $connection   = $this->resource->getConnection();
        $groupTable   = $this->resource->getTableName('customer_group');
        $query        = "SELECT * FROM $groupTable";
        $result       = $connection->fetchAll($query);

        $ids = [];
        foreach ($result as $res) {
            $ids[] = $res['customer_group_id'];
        }

        return $ids;
    }
}
