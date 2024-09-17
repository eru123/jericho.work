<?php

namespace App\Controller;

use App\Plugin\Mailer;
use App\Models\Newsletter as NewsletterModel;
use App\Models\Mails;
use eru123\helper\Format;
use eru123\router\Context;
use eru123\orm\Raw;

class Newsletter
{
    public static function add(Context $c)
    {
        $data = $c->json();
        $email = isset($data['email']) ? $data['email'] : null;
        $subs = isset($data['subscription']) ? $data['subscription'] : [];

        if (!$email) {
            http_response_code(400);
            return ['error' => 'Missing email'];
        }

        $mail = NewsletterModel::find(Raw::build('`email` = ?', [$email]));

        if ($mail && $mail['verified'] == 0) {
            NewsletterModel::update($mail['id'], [
                'subscriptions' => json_encode(array_unique(array_values($subs))),
            ]);

            $data = Verification::make([
                'email' => $email,
                'action' => 'newsletter_add',
                'type' => 'email',
            ], use_mailtpl('newsletter-add'));

            return [
                'success' => 'We have sent you a verification email',
            ];
        } else if ($mail) {
            return [
                'success' => 'Thank you for subscribing to our newsletter!',
            ];
        }

        NewsletterModel::insert([
            'subscriptions' => json_encode(array_unique(array_values($subs))),
            'verified' => 0,
            'email' => $email,
        ]);

        $data = Verification::make([
            'email' => $email,
            'type' => 'email',
            'action' => 'newsletter_add',
            'subject' => 'Newsletter - Email Verification',
        ], use_mailtpl('newsletter-add'));

        return [
            'success' => 'We have sent you a verification email',
        ];
    }

    public static function action_newsletter_add(array $data): bool
    {
        $email = isset($data['identifier']) ? $data['identifier'] : null;

        if (!$email) {
            return false;
        }

        $mail = NewsletterModel::find(Raw::build('`email` = ?', [$email]));
        if ($mail && $mail['verified'] == 0) {
            $update = NewsletterModel::update(Raw::build('`email` = ?', [$email]), [
                'verified' => 1,
            ]);

            if (!$update->rowCount()) {
                return false;
            }
        } else if ($mail) {
            return true;
        } else {
            return false;
        }

        $template = use_mailtpl('newsletter-verified');

        Mailer::queue([
            'to' => $email,
            'subject' => 'Newsletter - Email Verified',
            'body' => Format::template($template, [
                'code' => $data['code'],
                'email' => $email,
            ], FORMAT_TEMPLATE_DOLLAR_CURLY),
            'type' => Mails::TYPE_TRANSACTIONAL,
            'priority' => Mails::PRIORITY_HIGH
        ]);

        return true;
    }
}
