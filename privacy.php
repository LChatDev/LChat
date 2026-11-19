<?php
declare(strict_types=1);
define('LCHAT_LEGAL_PAGE', true);
$title = 'Privacy Policy';
$content = <<<'HTML'
<p>This policy describes how LChat processes information when using the messenger on chat.lbs.su.</p>
<h2>What data is processed</h2><p>Account data (name, login, email, photo and settings), password verification data, messages, attachments, information about groups, channels, contacts and participation in calls. During the operation of the service, they can also be processed IP-address, cookies sessions and technical journals.</p>
<h2>Why is this needed?</h2><p>For registration and login, delivery of messages and files, operation of calls, saving settings, protection against abuse, user support and compliance with legal requirements. Legal bases depend on the purpose and include enforcement of the user agreement, legal obligations of the operator and consent - when required.</p>
<h2>Data Visibility</h2><p>Messages and attachments are available to participants in the corresponding chat. Service administrators can view content to moderate and process requests. Do not send data to chats that you are not ready to entrust to participants and administration.</p>
<h2>Bots, AI and external services</h2><p>The bot receives messages in chats where it is present, within the limits of capabilities Bot API. When contacting an AI bot, the contents of the request may be transmitted to the AI provider, including Kilo, to generate a response. External bots and services may have their own terms and conditions for data processing; do not give them confidential information if you do not agree with it.</p>
<h2>Storage and protection</h2><p>Data is stored during the use of the service and further to the extent necessary for its operation, resolution of disputes and compliance with the law. Backups and technical logs may be retained. Access control measures and secure connection are used HTTPS; Absolute security of transmission and storage is not guaranteed.</p>
<h2>Your rights</h2><p>You can contact the controller to obtain information about the processing, clarify or delete data, limit processing and withdraw consent when processing is based on consent. The request is considered taking into account the requirements of the law and the need to confirm identity. Deleting individual messages from recipients and backups may not occur instantly.</p>
<h2>Changes</h2><p>The current edition is posted on this page. If significant changes occur, users may be shown a notification in the service.</p>
HTML;
require __DIR__ . '/legal-page.php';
