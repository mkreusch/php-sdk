<?php
/**
 * This file provides an example implementation of the EPS payment type.
 *
 * Copyright (C) 2018 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        Heidelpay UI Examples
    </title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"
            integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://static.heidelpay.com/v1/heidelpay.css" />
    <script type="text/javascript" src="https://static.heidelpay.com/v1/heidelpay.js"></script>
</head>

<body style="margin: 70px 70px 0;">
<h3>Example data:</h3>
<strong>Erste Bank Sparkasse</strong>
<ul>
    <li>Username: 108256743</li>
    <li>Password: npydemo</li>
    <li>TAN: 1111</li>
</ul>

<strong>&Auml;rztebank</strong>
<ul>
    <li>Follow all steps shown on screen without entering any data.</li>
</ul>

<form id="payment-form" class="heidelpayUI form" novalidate>
    <div id="example-eps" class="field"></div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button class="heidelpayUI primary button fluid" type="submit">Pay</button>
</form>

<script>
    // Creating a heidelpay instance with your public key
    let heidelpayInstance = new heidelpay('s-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa');

    // Creating an EPS instance
    let EPS = heidelpayInstance.EPS();

    // Rendering input fields
    EPS.create('eps', {
        containerId: 'example-eps'
    });

    let $errorHolder = $('#error-holder');
    // Handling payment form's submission
    let form = document.getElementById('payment-form');
    // Handle EPS form submission.
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        // Creating a EPS resource
        EPS.createResource()
            .then(function(result) {
                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'resourceId');
                hiddenInput.setAttribute('value', result.id);
                form.appendChild(hiddenInput);
                form.setAttribute('method', 'POST');
                form.setAttribute('action', '<?php echo CONTROLLER_URL; ?>');

                // Submitting the form
                form.submit();
            })
            .catch(function(error) {
                $errorHolder.html(error.message);
            })
    });
</script>
</body>
</html>
