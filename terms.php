<?php
declare(strict_types=1);
define('LCHAT_LEGAL_PAGE', true);
$title = 'User Agreement';
$content = <<<'HTML'
<p>The agreement describes the terms of use of the messenger LChat on chat.lbs.su. Please also read the privacy policy.</p>
<h2>Service and account</h2><p>LChat provides chats, groups, channels, file sharing, calling and bot capabilities. Availability of individual features may vary. The user is responsible for the safety of his password and actions in his account; notify the administration if you suspect unauthorized access.</p>
<h2>Terms of use</h2><p>Violations of the law, distribution of malicious code, spam, attempts to obtain other people’s data or disrupt the operation of the service are prohibited. The user is responsible for the materials posted and must have the rights to distribute them.</p>
<h2>Moderation and data</h2><p>The administration can check the content of chats for moderation and consider complaints. In case of violations, access and materials may be limited or removed. Data is processed in accordance with the privacy policy; bots and external services can receive information that is transmitted to them.</p>
<h2>Premium and payment</h2><p>Premium is presented as a planned feature. The payment system is not connected; registration, debiting of funds and provision of paid benefits are not available. Preliminary prices published on <a href="pay.php">ticket page and Premium</a>. The scope of services and final conditions will be clarified before the start of sales. The order of requests is described on the page <a href="rules.php#refund">rules and returns</a>.</p>
<h2>Return</h2><p>To request a refund, write to <a href="mailto:admin@example.com">admin@example.com</a> and indicate the login, date, amount, reason for the request and payment ID or check, if available. Don’t share your password, PIN-code, CVC/CVV or codes from SMS.</p>
<p>If there are grounds for a refund, the funds will be returned in the same way as payment was made, within 10 calendar days from the date of receipt of the request, unless the applicable law provides for a different mandatory period. The actual credit after the refund is sent depends on the bank or payment provider.</p>
<p>The consumer has the right to withdraw from the contract for the provision of services in the manner prescribed by law; in this case, the actually incurred and justified expenses of the performer may be taken into account within the limits permitted by law. The return conditions do not limit the consumer’s rights granted by mandatory legal provisions. Currently the payment system is not connected and purchases are not available.</p>
<h2>Availability and responsibility</h2><p>Subject to interruptions, errors and changes in functionality. The user should save important data independently. These terms and conditions do not exclude or limit the rights granted to the user by mandatory provisions of applicable law..</p>
<h2>Change of conditions and treatment</h2><p>The new edition is posted on this page. The operator’s contact is listed below. Processing of requests is carried out in accordance with applicable law.</p>
<p>Telephone: <a href="tel:[indicate support phone number]">[indicate support phone number]</a>.</p>
HTML;
require __DIR__ . '/legal-page.php';
