<?php

namespace Magecomp\Ordertracking\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const ORDERTRACKING_GENERAL_ENABLE = 'ordertracking/general/enable';

    const ORDERTRACKING_BLUEDART_ENABLED = 'ordertracking/bluedart/enable';
    const ORDERTRACKING_BLUEDART_LOGINID = 'ordertracking/bluedart/loginid';
    const ORDERTRACKING_BLUEDART_LKEY = 'ordertracking/bluedart/licencekey';

    const ORDERTRACKING_DELHIVERY_ENABLED = 'ordertracking/delhivery/enable';

    const ORDERTRACKING_USPS_USERID = 'ordertracking/usps/userid';

    const ORDERTRACKING_TIRUPATI_ENABLE = 'ordertracking/shreetirupaticourier/enable';

    const ORDERTRACKING_TRACKON_ENABLE = 'ordertracking/trackoncourier/enable';
    const ORDERTRACKING_TRACKON_APPKEY = 'ordertracking/trackoncourier/appkey';
    const ORDERTRACKING_TRACKON_USERID = 'ordertracking/trackoncourier/userid';
    const ORDERTRACKING_TRACKON_PASSWORD = 'ordertracking/trackoncourier/password';

    const ORDERTRACKING_PROFESSIONAL_ENABLE = 'ordertracking/theprofessional/enable';
    const ORDERTRACKING_PROFESSIONAL_CLIENTID = 'ordertracking/theprofessional/clientid';
    const ORDERTRACKING_PROFESSIONAL_PASSWORD = 'ordertracking/theprofessional/password';

    const ORDERTRACKING_SHIPROCKET_ENABLE = 'ordertracking/shiprocket/enable';
    const ORDERTRACKING_SHIPROCKET_USEREMAIL = 'ordertracking/shiprocket/user_email';
    const ORDERTRACKING_SHIPROCKET_PASSWORD = 'ordertracking/shiprocket/user_password';

    public function isEnable()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_GENERAL_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function isBluedart()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_BLUEDART_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isDelhivery()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_DELHIVERY_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isTirupatiCourier()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_TIRUPATI_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function isTrackonCourier()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_TRACKON_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function isProfessionalCourier()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_PROFESSIONAL_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getTPCClientId()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_PROFESSIONAL_CLIENTID, ScopeInterface::SCOPE_STORE);
    }

    public function getTPCPassword()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_PROFESSIONAL_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    public function isShipRocket()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_SHIPROCKET_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getBluedartData($waybillnumber)
    {
        $html = '';
        $licencekey = $this->getBluedartLicenceKey();
        $loginid = $this->getBluedartLoginId();
        try {
            if ($waybillnumber != '' && $licencekey != '' && $loginid != '') {
                $version = '1.3';
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    /*CURLOPT_URL => "http://www.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&loginid=" . $loginid . "&awb=awb&numbers=" . $waybillnumber . "&format=XML&lickey=" . $licencekey . "&verno=" . $version . "&scan=1",*/
                    CURLOPT_URL => "https://api.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&loginid=" . $loginid . "&awb=awb&numbers=" . $waybillnumber . "&format=XML&lickey=" . $licencekey . "&verno=" . $version . "&scan=1",
                    CURLOPT_USERAGENT => "Codular Sample cURL Request"
                ));

                $result = curl_exec($curl);
                curl_close($curl);
                $result = simplexml_load_string($result);
                if ($result->Shipment) {
                    if ($result->Shipment->StatusType == 'NF') {
                        $res_error_type = "NF";
                        $res_error = __("Incorrect Waybill number or No Information");
                        if ($result->Shipment->StatusType) {
                            $res_error_type = $result->Shipment->StatusType;
                        }
                        if ($result->Shipment->Status) {
                            $res_error = $result->Shipment->Status;
                        }
                        $html .= "
                                  <div class='divTable'>
                                  <div class='headRow'>
                                  <div class='divCell pincodes' align='center'>
                                  <p><span><b>" . __('Shipment Tracking') . "</b></span></p>
                                  <p><span><b>" . __('Status Type') . "</b></span><span class='error_msg'>" . $res_error_type . "</span></p>
                                  <p><span><b>" . __('Description') . "</b></span><span class='error_msg'>" . $res_error . "</span></p>
                                  </div>
                                  </div>
                                  </div>";

                    } else {
                        $scan_details = $result->Shipment->Scans->ScanDetail;
                        $scanhtml = "";
                        $r = 0;
                        $scanhtml .= "<div class='alldate'>";
                        $scanhtml .= " <p><span><b>" . __('Status : ') . "</b>" . $result->Shipment->Status . "</span></p>
                                                  <div class='scan-data'>
                                                      <div class='scan-row first-row'>
                                                          <p>" . __('Location') . "</p>
                                                          <p>" . __('Details') . "</p>
                                                          <p>" . __('Date') . "</p>
                                                          <p>" . __('Time') . "</p>
                                                      </div>
                                                      ";
                        foreach ($scan_details as $value) {
                            $r++;
                            $scanhtml .= "<div class='scan-row'>
                                                        <p>" . $value->ScannedLocation . "</p>
                                                        <p>" . $value->Scan . "</p>
                                                        <p>" . $value->ScanDate . "</p>
                                                        <p>" . $value->ScanTime . "</p>
                                                    </div>";
                        }

                        $scanhtml .= '</div>';
                        $scanhtml .= '</div>';
                        $html .= $scanhtml;
                    }
                } else {
                    $html .= '<p>' . __('Could not find this order with us. Please check the AWB/order ID entered.') . '</p>';
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getBluedartLicenceKey()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_BLUEDART_LKEY, ScopeInterface::SCOPE_STORE);
    }

    public function getBluedartLoginId()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_BLUEDART_LOGINID, ScopeInterface::SCOPE_STORE);
    }

    public function getDelhiveryData($waybillnumber)
    {
        $html = '';
        try {
            if ($waybillnumber != '') {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => "https://uxxbqylwa3.execute-api.ap-southeast-1.amazonaws.com/prod/track?waybillId=" . $waybillnumber,
                    CURLOPT_USERAGENT => "Codular Sample cURL Request"
                ));

                $result = curl_exec($curl);
                curl_close($curl);
                $arr = json_decode($result, true);
                if ($arr['data'][0]['status']['status'] == 'IN_TRANSIT' || $arr['data'][0]['status']['status'] == 'WAITING_PICKUP' || $arr['data'][0]['status']['status'] == 'PICKUP') {
                    $html .= '<div class="alldate">';
                    $html .= '<p class="status-class">' . __('STATUS : ') . $arr['data'][0]['status']['status'] . '</p>';

                    $html .= '<div class="scan-data">';
                    $html .= '<div class="scan-row first-row">';
                    $html .= '<p>' . __('DateTime') . '</p>';
                    $html .= '<p>' . __('Location') . '</p>';
                    $html .= '<p>' . __('Instructions') . '</p>';
                    $html .= '<p>' . __('Status') . '</p>';
                    $html .= '</div>';
                    foreach ($arr['data'][0]['scans'] as $scan) {
                        $html .= '<div class="scan-row">';
                        $html .= '<p>' . $scan['scanDateTime'] . '</p>';
                        $html .= '<p>' . $scan['scannedLocation'] . '</p>';
                        $html .= '<p>' . $scan['instructions'] . '</p>';
                        $html .= '<p>' . $scan['status'] . '</p>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                } else {
                    $html .= '<p>' . __('Could not find this order with us. Please check the waybill/order ID entered.') . '</p>';
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getUspsData($waybillnumber)
    {
        $html = '';
        $userId = $this->getUSPSUserId();
        try {
            if ($waybillnumber != '' && $userId != '') {
                $xml = urlencode('<TrackRequest USERID="' . urlencode($userId) . '"><TrackID ID="' . $waybillnumber . '"></TrackID></TrackRequest>');
                $response = file_get_contents($url = 'http://production.shippingapis.com/ShippingAPI.dll?API=TrackV2&XML=' . $xml);

                $result = simplexml_load_string($response);

                if ($result->TrackInfo->TrackDetail) {
                    $html = '<p class="status-class">' . $result->TrackInfo->TrackSummary . '</p>';

                    $html .= '<div class="scan-data">';
                    $html .= '	<div class="scan-row first-row usps">
								<p>' . __('Detail') . '</p>
							</div>';
                    foreach ($result->TrackInfo->TrackDetail as $detail) {
                        $html .= '<div class="scan-row usps">
									<p>' . $detail . '</p>
								</div>';
                    }

                    $html .= '</div>';
                } else {
                    $html .= '<p>' . __('Could not find this order with us. Please check the track/order ID entered.') . '</p>';
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getUSPSUserId()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_USPS_USERID, ScopeInterface::SCOPE_STORE);
    }

    public function getDhlData($waybillnumber)
    {
        $html = '';
        try {
            if ($waybillnumber != '') {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => "http://www.dhl.co.in/shipmentTracking?AWB=" . $waybillnumber . "&countryCode=in&languageCode=en&_=1514867530356",
                    CURLOPT_USERAGENT => "Codular Sample cURL Request"
                ));

                $result = curl_exec($curl);
                curl_close($curl);
                $final = json_decode($result, true);
                if ($final['results']) {
                    $html .= '<p class="status-class"></p>';

                    if (array_key_exists("results", $final)) {
                        $html .= '<div class="scan-data">';
                        $html .= '	<div class="scan-row first-row dhl">
									<p>' . __('Waybill') . '</p>
									<p>' . __('Origin Service Area') . '</p>
									<p>' . __('Destination Service Area') . '</p>
								</div>';
                        foreach ($final['results'] as $waybill) {
                            $html .= '<div class="scan-row dhl">
										<p>' . $waybill['id'] . '</p>
										<p>' . $waybill['origin']['value'] . '</p>
										<p>' . $waybill['destination']['value'] . '</p>
									</div>';
                        }
                        $html .= '</div>';
                    }
                } else
                    $html .= '<p>' . __('Could not find this order with us. Please check the AWB/order ID entered.') . '</p>';
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getTirupatiCourierData($waybillnumber)
    {
        $html = '';
        try {
            if ($waybillnumber != '') {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => "http://shreetirupaticourier.net/STCS_API.aspx?AWBno=" . $waybillnumber,
                    CURLOPT_USERAGENT => "Codular Sample cURL Request"
                ));
                $result = curl_exec($curl);
                curl_close($curl);

                $result = simplexml_load_string($result);
                if ($result->Status == 'AWB No. Not Found') {
                    $res_error = $result->Status;
                    $html .= " <div class='divTable'>
							  <div class='headRow'>
							  <div class='divCell pincodes'>
							  <p><span><b>" . __('Shipment Tracking') . "</b></span></p>
							  <p><span><b>" . __('Description') . " </b></span><span class='error_msg'>" . __($res_error) . "</span></p>
							  </div>
							  </div>
							  </div>";
                } else {
                    $scan_details = $result->TransitHistory->Transit;
                    $scanhtml = "<div class='alldate'>";
                    if (isset($result->BookingData)) {
                        $scanhtml .= "<p><span><b>" . __('Status :') . " </b>" . $result->Status . "</span></p>
                                    <div class='scan-data booking-data'>
                                          <div class='scan-row first-row'>
                                              <p>" . __('Reciever Name') . "</p>
                                              <p>" . __('From') . "</p>
                                              <p>" . __('To') . "</p>
                                              <p>" . __('Booking Date') . "</p>
                                              <p>" . __('Booking Time') . "</p>
                                          </div>
                                          <div class='scan-row'>
                                            <p>" . $result->BookingData->ReceiverName . "</p>
                                            <p>" . $result->BookingData->FromCenter . "</p>
                                            <p>" . $result->BookingData->ToCenter . "</p>
                                            <p>" . $result->BookingData->BookingDate . "</p>
                                            <p>" . $result->BookingData->BookingTime . "</p>
                                        </div>
									</div> ";
                    }
                    if (isset($scan_details)) {
                        $scanhtml .= " <p><span><b>" . __('History :') . " </b></p>
											  <div class='scan-data'>
												  <div class='scan-row first-row'>
													  <p>" . __('Location') . "</p>
													  <p>" . __('Details') . "</p>
													  <p>" . __('Date') . "</p>
													  <p>" . __('Time') . "</p>
												  </div>

												  ";
                        foreach ($scan_details as $value) {
                            $scanhtml .= "<div class='scan-row'>
													<p>" . $value->Route . "</p>
													<p>" . $value->Job . "</p>
													<p>" . $value->TransitDate . "</p>
													<p>" . $value->TransitTime . "</p>
												</div>";
                        }

                        $scanhtml .= '</div>';
                    }
                    $scanhtml .= '</div>';
                    $html .= $scanhtml;
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getTrackonCourierData($waybillnumber)
    {
        $userId = $this->getTrackonUserId();
        $password = $this->getTrackonPassword();
        $appKey = $this->getTrackonAppKey();
        $url = "http://trackoncourier.com/Api/api/t1/CustDetailTracking";
        $url .= "?AWBNo=" . $waybillnumber . "&AppKey=" . $appKey . "&userID=" . $userId . "&Password=" . $password;
        $html = '';
        try {
            if ($waybillnumber != '') {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => $url,
                    CURLOPT_USERAGENT => "Codular Sample cURL Request"
                ));
                $result = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($result, true);
                if (isset($result['ResponseStatus'])) {
                    if ($result['ResponseStatus']['Message'] == "Success") {
                        $scan_details = $result['lstDetails'];
                        $scanhtml = "<div class='alldate'>";
                        if (isset($result['summaryTrack'])) {
                            $scanhtml .= "<p><span><b>" . __('Status :') . "</b>" . $result['summaryTrack']['CURRENT_STATUS'] . "</span></p>
                                    <div class='scan-data'>
                                          <div class='scan-row first-row'>
                                              <p>" . __('Origin') . "</p>
                                              <p>" . __('Destination') . "</p>
                                              <p>" . __('Booking Date') . "</p>
                                              <p>" . __('Service Type') . "</p>
                                          </div>
                                          <div class='scan-row'>
                                            <p>" . $result['summaryTrack']['ORIGIN'] . "</p>
                                            <p>" . $result['summaryTrack']['"DESTINATION'] . "</p>
                                            <p>" . $result['summaryTrack']['BOOKING_DATE'] . "</p>
                                            <p>" . $result['summaryTrack']['SERVICE_TYPE'] . "</p>
                                        </div>
									</div> ";
                        }
                        if (isset($scan_details)) {
                            $scanhtml .= " <p><span><b>" . __('History :') . "</b></p>
											  <div class='scan-data booking-data'>
												  <div class='scan-row first-row'>
													  <p>" . __('Location') . "</p>
													  <p>" . __('Details') . "</p>
													  <p>" . __('Date') . "</p>
													  <p>" . __('Time') . "</p>
													  <p>" . __('Tracking Code') . "</p>
												  </div>
												  ";
                            foreach ($scan_details as $value) {
                                $scanhtml .= "<div class='scan-row'>
													<p>" . $value['CURRENT_CITY'] . "</p>
													<p>" . $value['CURRENT_STATUS'] . "</p>
													<p>" . $value['EVENTDATE'] . "</p>
													<p>" . $value['EVENTTIME'] . "</p>
													<p>" . $value['TRACKING_CODE'] . "</p>
												</div>";
                            }

                            $scanhtml .= '</div>';
                        }
                        $scanhtml .= '</div>';
                        $html .= $scanhtml;
                    } else {
                        $res_error = $result['ResponseStatus']['Message'];
                        $html .= "<div class='divTable'>
                                  <div class='headRow'>
                                  <div class='divCell'>
                                  <p><span><b>" . __('Shipment Tracking') . "</b></span></p>
                                  <p><span><b>" . __('Description') . " </b></span><span class='error_msg'>" . __($res_error) . "</span></p>
                                  </div>
                                  </div>
                                  </div>";
                    }
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }

        return $html;
    }

    public function getTrackonUserId()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_TRACKON_USERID, ScopeInterface::SCOPE_STORE);
    }

    public function getTrackonPassword()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_TRACKON_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    public function getTrackonAppKey()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_TRACKON_APPKEY, ScopeInterface::SCOPE_STORE);
    }

    public function getProfessionalCourierData($waybillnumber)
    {
        $html = 'Professional Courrier Data - ';
        $html .= __("Something Went Wrong, See error Log");
        return $html;
    }

    public function getShipRocket($waybillnumber)
    {

        $html = '';
        $user_email = $this->getShipRocketUserEmail();
        $password = $this->getShipRocketPassword();
        try {

            if ($waybillnumber != '' && $user_email != '' && $password != '') {

                $version = '1.3';
                $curl = curl_init();
                $postData = array(
                    'email' => $user_email,
                    'password' => $password,
                );

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/auth/login",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "{\n    \"email\": \"$user_email\",\n    \"password\": \"$password\"\n}",
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json"
                    ),
                ));

                $result = curl_exec($curl);

                $result_decode = json_decode($result, true);
                curl_close($curl);
                $token = $result_decode["token"];
                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                );
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/courier/track/awb/" . $waybillnumber,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ));

                $result = curl_exec($curl);

                curl_close($curl);

                $result = json_decode($result, true);
                if ($result['tracking_data']['track_status'] == 1) {
                    $scan_details = $result['tracking_data']['shipment_track_activities'];
                    $scanhtml = "<div class='alldate'>";
                    if (isset($result['tracking_data'])) {
                        $scanhtml .= "<p><span><b>" . __('Status :') . "</b>" . $result['tracking_data']['shipment_track']['0']['current_status'] . "</span></p>
                                    <div class='scan-data'>
                                          <div class='scan-row first-row'>
                                              <p>" . __('Origin') . "</p>
                                              <p>" . __('Destination') . "</p>
                                              <p>" . __('Booking Date') . "</p>
                                              <p>" . __('Service Type') . "</p>
                                          </div>
                                          <div class='scan-row'>
                                            <p>" . $result['tracking_data']['shipment_track']['0']['origin'] . "</p>
                                            <p>" . $result['tracking_data']['shipment_track']['0']['destination'] . "</p>
                                            <p>" . $result['tracking_data']['shipment_track']['0']['pickup_date'] . "</p>
                                            <p>" . $result['tracking_data']['shipment_track']['0']['order_id'] . "</p>
                                        </div>
                                    </div> ";
                    }
                    if (isset($scan_details)) {
                        $scanhtml .= " <p><span><b>" . __('History :') . "</b></p>
                                              <div class='scan-data booking-data'>
                                                  <div class='scan-row first-row'>
                                                      <p>" . __('Date & Time') . "</p>
                                                      <p>" . __('Activity') . "</p>
                                                      <p>" . __('Location') . "</p>
                                                  </div>
                                                  ";
                        foreach ($scan_details as $value) {
                            $scanhtml .= "<div class='scan-row'>
                                                    <p>" . $value['date'] . "</p>
                                                    <p>" . $value['activity'] . "</p>
                                                    <p>" . $value['location'] . "</p>
                                                </div>";
                        }

                        $scanhtml .= '</div>';
                    }
                    $scanhtml .= '</div>';
                    $html .= $scanhtml;
                } else {
                    $res_error = $result['tracking_data']['error'];
                    $html .= "<div class='divTable'>
                                  <div class='headRow'>
                                  <div class='divCell'>
                                  <p><span><b>" . __('Shipment Tracking') . "</b></span></p>
                                  <p><span><b>" . __('Description') . " </b></span><span class='error_msg'>" . __($res_error) . "</span></p>
                                  </div>
                                  </div>
                                  </div>";
                }
            }
        } catch (\Exception $e) {
            $html = __("Something Went Wrong, See error Log");
            return $html;
        }
        return $html;
    }

    public function getShipRocketUserEmail()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_SHIPROCKET_USEREMAIL, ScopeInterface::SCOPE_STORE);
    }

    public function getShipRocketPassword()
    {
        return $this->scopeConfig->getValue(self::ORDERTRACKING_SHIPROCKET_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

}
