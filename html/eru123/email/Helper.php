<?php

namespace eru123\email;

class Helper
{
    static $validatedDomains = [];
    static $validatedEmails = [];

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
    }

    public static function validateDomain(string $email): bool
    {
        $domain = explode('@', $email)[1];

        if (isset(static::$validatedDomains[$domain])) {
            return static::$validatedDomains[$domain];
        }

        $dns = dns_get_record($domain, DNS_MX);

        if (empty($dns)) {
            return static::$validatedDomains[$domain] = false;
        }

        return static::$validatedDomains[$domain] = true;
    }

    public static function validateRecipient(string $email): bool
    {
        if (isset(static::$validatedEmails[$email])) {
            return static::$validatedEmails[$email];
        }

        if (!static::validateEmail($email) || !static::validateDomain($email)) {
            return static::$validatedEmails[$email] = false;
        }

        return static::$validatedEmails[$email] = true;
    }
}
