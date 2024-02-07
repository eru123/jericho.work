<?php

return <<<HTML
<!DOCTYPE html>
<html lang="en">
<body>
    <p>Hi there!</p>
    <p>Your email address (\${ email }) has been used to subscribe to our newsletter.</p>
    <p>To verify your email address, please click or copy and paste the link below to your browser:</p>
    <p><a href="\${ link }">\${ link }</a></p>
    <p>Best regards,<br>SKIDD PH</p>
    <br>
    <br>
    <p><small>This is an automated message. Please do not reply to this email.</small></p>
    <p><small>If you did not register on our site using this email address, please ignore this email.</small></p>
</body>
</html>
HTML;