<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Mailer;
use App\Plugin\MC;
use App\Models\Mails;
use App\Models\Smtps;

$args = getopt('q:');

if (isset($args['q']) && !empty($args['q'])) {
    $id = $args['q'];
    $mail = Mails::find($id);
    if (!$mail) {
        echo "Mail queue not found.", PHP_EOL;
        exit(1);
    }

    $mail_identifier = Mailer::CACHE_PREFIX . '0_' . time();
    $limit = env('SMTP_LIMIT_PER_SECOND', 14);
    if ($mail['sender_id'] == 0) {
        $mailer = Mailer::instance('default', null, false);
    } else {
        $smtp = Smtps::find($mail['smtp_id']);
        if (!$smtp) {
            echo "SMTP not found.", PHP_EOL;
            exit(1);
        }

        $mail_identifier = Mailer::CACHE_PREFIX . $mail['sender_id'] . '_' . time();
        $mailer = Mailer::instance((string) $mail['sender_id'], $smtp, false);
        $limit = $smtp['limit'];
    }

    $mc = MC::instance();
    $ctr = intval($mc->get($mail_identifier));
    if ($ctr >= $limit) {
        echo "SMTP limit reached.", PHP_EOL;
        exit(1);
    }

    $mc->set($mail_identifier, $ctr + 1, 60);

    try {
        $newdata = $mailer->send($mail);
        Mails::update($mail['id'], [
            'status' => $newdata['status'],
            'message_id' => $newdata['message_id'],
            'response' => $newdata['response']
        ]);
    } catch (Exception $e) {
        $requeue = static::queue([
            'parent_id' => $mail['id'],
            'sender_id' => $mail['sender_id'],
            'user_id' => $mail['user_id'],
            'to' => $mail['to'],
            'cc' => $mail['cc'],
            'bcc' => $mail['bcc'],
            'subject' => $mail['subject'],
            'body' => $mail['body'],
            'priority' => Mails::PRIORITY_NONE,
            'type' => $mail['type'],
            'status' => Mails::STATUS_QUEUE
        ] + (@$mail['meta'] ?? []));
        if ($requeue) {
            Mails::update($mail['id'], [
                'status' => Mails::STATUS_FAILED,
                'response' => [
                    'provider' => static::$pool[$smtp->pool_key]->provider() ?? 'Unknown',
                    'logs' => static::$pool[$smtp->pool_key]->full_logs(),
                    'error' => $e->getMessage()
                ]
            ]);
        }
    }
}
