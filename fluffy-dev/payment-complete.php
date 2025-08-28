<?php
/**
 * Template Name: Payment Complete
 *
 * This template handles the frontend response from the 2C2P payment gateway.
 */

get_header();
?>

<div class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="container mx-auto">
        <div class="card bg-base-100 shadow-xl max-w-2xl mx-auto">
            <div class="card-body">
                <?php
                // --- 2C2P Configuration ---
                // IMPORTANT: Replace with your actual Secret Key
                $secret_key = get_field('2c2p_secret_key', 'option');

                if (isset($_REQUEST["paymentResponse"])) {
                    $response = $_REQUEST["paymentResponse"];
                    $reponsePayLoadXML = base64_decode($response);
                    $xmlObject = simplexml_load_string($reponsePayLoadXML);

                    if ($xmlObject) {
                        $payloadB64 = $xmlObject->payload;
                        $payloadXML = base64_decode($payloadB64);
                        $signatureXML = $xmlObject->signature;

                        $base64EncodedPayloadResponse = base64_encode($payloadXML);
                        $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse, $secret_key, false));

                        if ($signatureXML == $signatureHash) {
                            $payloadObj = simplexml_load_string($payloadXML);
                            if ($payloadObj) {
                                $payment_status = (string)$payloadObj->respCode;
                                $invoice_no = (string)$payloadObj->invoiceNo;
                                $channel_response_desc = (string)$payloadObj->respDesc;

                                if ($payment_status === '0000') { // Success code for API v4.1 is '0000'
                                    // Payment successful
                                    echo '<div class="text-center">';
                                    echo '<div class="inline-block bg-green-100 text-green-600 p-3 rounded-full">';
                                    echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                                    echo '</div>';
                                    echo '<h1 class="text-2xl font-bold text-green-600 mt-2">Payment Successful!</h1>';
                                    echo '<p class="mt-2">Your fine has been paid. Thank you.</p>';
                                    echo '<p><strong>Invoice:</strong> ' . esc_html($invoice_no) . '</p>';
                                    echo '</div>';
                                } else {
                                    // Payment failed or was cancelled
                                    echo '<div class="text-center">';
                                    echo '<div class="inline-block bg-red-100 text-red-600 p-3 rounded-full">';
                                    echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                                    echo '</div>';
                                    echo '<h1 class="text-2xl font-bold text-red-600 mt-2">Payment Failed</h1>';
                                    echo '<p class="mt-2">Your payment could not be processed. Please try again.</p>';
                                    echo '<p><strong>Status:</strong> ' . esc_html($channel_response_desc) . '</p>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="alert alert-error"><span><strong>Error:</strong> Could not parse payment payload.</span></div>';
                            }
                        } else {
                            // If Signature does not match
                            echo '<div class="alert alert-error"><span><strong>Security Error:</strong> Invalid response signature. The payment response may have been tampered with.</span></div>';
                        }
                    } else {
                         echo '<div class="alert alert-error"><span><strong>Error:</strong> Could not parse payment response.</span></div>';
                    }
                } else {
                    // No paymentResponse data
                    echo '<div class="alert alert-warning"><span>No payment information received.</span></div>';
                }
                ?>
                <div class="mt-6 text-center">
                    <a href="<?php echo home_url('/service-control/'); ?>" class="btn btn-primary">Return to Service Page</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?>
