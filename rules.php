<?php
declare(strict_types=1);
define('LCHAT_LEGAL_PAGE', true);
$title = 'Rules, payment and returns';
$content = <<<'HTML'
<p><strong>Preliminary edition of November 8, 2026.</strong> Here are the rules LChat, information about tickets and Premium, as well as the procedure for applying for a refund. Page complements <a href="terms.php">user agreement</a> and <a href="privacy.php">privacy policy</a>.</p>
<p class="notice"><strong>Payment not yet connected.</strong> Buy tickets or issue Premium not allowed now. Listed on the page <a href="pay.php">prices and features Premium</a> preliminary; Available options and terms will be confirmed prior to sales. Tickets are still issued by the administration.</p>

<h2>1. Terms of use LChat</h2>
<p>LChat provides messaging and file sharing, chats, groups, channels, calls and bots. Use the service legally and respect other members. Fraud, spam, threats, distribution of malicious code, hacking attempts, publication of other people’s personal data without legal grounds and other illegal use are prohibited..</p>
<p>The user is responsible for the security of his account and the materials he posts. Upload and distribute only content that you have the rights or other legal rights to use. In case of violations, the administration may restrict access or remove materials in accordance with the agreement and applicable law.</p>

<h2>2. Tickets and gifts</h2>
<p>Tickets - internal units LChat, which are used to purchase gifts within the service. Now you cannot buy them for money; the administration can issue them to users. The number of tickets and the value of the gift are shown before the transaction is confirmed. The gift can be added to your collection or sent to another user.</p>
<p>Before sending, check the recipient: after confirmation, the gift appears in his collection. If you make a mistake or have a technical problem, please contact support. The ability to remedy depends on the circumstances and does not affect your rights under the law..</p>

<h2>3. Premium and future payment</h2>
<p>Premium and its benefits are still being developed. Preliminary price — 199 ₽ per month; guideline for tickets - 10 tickets per 5 ₽. Before the start of sales, the final composition, price, delivery procedure, terms and other essential conditions will be published. There are no automatic debits at this time..</p>
<p>If automatic renewal becomes available in the future, its conditions, cost, frequency and method of deactivation will be separately presented until the user agrees. Payment methods and contractor details will be indicated before accepting payments..</p>

<h2 id="refund">4. Refusal and refund</h2>
<p>Since the payment system is not connected, paid purchases of tickets and Premium through this page are currently not possible. Tickets issued free of charge are not a cash payment. If in the future you have a question about payment, non-received service, erroneous or repeated debit, please send a support request to <a href="mailto:admin@example.com">admin@example.com</a>.</p>
<p>Please indicate your login in your request LChat, date and amount of payment, description of the problem and transaction ID or receipt if you have one. Don’t send your password, PIN-code, full card number, CVC/CVV or codes from SMS. If necessary, support will clarify the information necessary to confirm the payment and consider the request.</p>
<p>When consumer protection rules apply to the relationship, the user has the right to withdraw from the service contract in the manner prescribed by law. In case of such a refusal, the actually incurred and justified expenses of the performer associated with the fulfillment of obligations may be taken into account, to the extent permitted by law. Justified demands for improper provision of services are considered in accordance with the law. Your statutory rights are unaffected by these rules.</p>
<p>The basis, amount, method and period of return are determined taking into account the circumstances of the application, method of payment and the requirements of applicable law. For certain claims related to violation of deadlines or deficiencies in the service, Article 31 of the Law of the Russian Federation “On the Protection of Consumer Rights” provides for a ten-day period for satisfaction from the date of presentation of the claim. The corresponding legal deadlines apply to other requests..</p>

<h2>5. Data and security</h2>
<p>Data processing is described in the <a href="privacy.php">privacy policy</a>. Messages are available to participants in the corresponding chat; the administration can view them for moderation and processing of requests, as specified in the agreement. If you suspect hacking or erroneous access, please report to support.</p>

<h2>6. Contact and changes</h2>
<p>For questions about rules, payments and returns, write to <a href="mailto:admin@example.com">admin@example.com</a>. The current version of documents is posted on the website. Before paid features are launched, artist details and final terms must be published.</p>
<p>Consumer rights information is available on <a href="https://zpp.rospotrebnadzor.ru/npa/federal/192115" target="_blank" rel="noopener noreferrer">Rospotrebnadzor portal</a>.</p>
HTML;
require __DIR__ . '/legal-page.php';
