<?php

namespace App\Controller;

use App\Plugin\DB;
use eru123\orm\Raw;
use eru123\router\Context;
use Error;
use PDO;

class MailTemplate extends Controller
{
    public function create(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $data = $c->json();

        if (empty($data['template'])) {
            throw new Error('Template is required', 400);
        }

        $template = $data['template'];
        $code = $data['code'] ?: NULL;
        $default = $data['default'] ?: NULL;
        $active = @$data['active'] ?? 1;

        if (!is_string($data['template'])) {
            throw new Error('Template must be a string', 400);
        }

        if (!is_string($data['code']) && !is_null($data['code'])) {
            throw new Error('Template Code must be a string', 400);
        }

        if (!empty($default) && !is_array($default)) {
            throw new Error('Default data must be an object', 400);
        }

        if (empty($default) && !is_null($default)) {
            $default = NULL;
        }

        if ($code && DB::instance()->query('SELECT id, user_id, code FROM mail_templates WHERE code = ? AND user_id = ? AND deleted_at IS NULL', [$code, $user_id])->fetch(PDO::FETCH_ASSOC)) {
            throw new Error('Template Code already exists', 400);
        }

        $data = [
            'user_id' => $user_id,
            'code' => $code,
            'template' => $template,
            'active' => (is_bool($active) ? $active : $active == 1) ? 1 : 0,
            'default' => is_null($default) ? NULL : json_encode($default),
        ];

        $stmt = DB::instance()->insert('mail_templates', $data);
        $affected = $stmt->rowCount();

        if ($affected < 1) {
            throw new Error('Failed to create template', 500);
        }

        $inserted = static::find_one_where_equal([
            'id' => DB::instance()->pdo()->lastInsertId(),
        ]);

        if (!$inserted) {
            throw new Error('Failed to create template', 500);
        }

        return [
            'success' => 'Template created',
            'data' => $inserted,
        ];
    }

    public function find_one_where_equal(array $data)
    {
        $where = [];
        $params = [];
        foreach ($data as $key => $value) {
            $where[] = "{$key} = ?";
            $params[] = $value;
        }

        $where = implode(' AND ', $where);
        $template = DB::instance()->query("SELECT * FROM mail_templates WHERE {$where} AND deleted_at IS NULL", $params)->fetch(PDO::FETCH_ASSOC);
        if ($template) {
            $template['default'] = json_decode($template['default'], true);
            $template['active'] = (bool) $template['active'];
        }

        return $template;
    }

    public function view(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $template_id = @$c->params['id'] ?? NULL;
        $code = @$c->params['code'] ?? NULL;

        $where = [
            'user_id' => $user_id,
        ];

        if ($template_id) {
            $where['id'] = $template_id;
        } else if ($code) {
            $where['code'] = $code;
        } else {
            throw new Error('Template ID or Code is required', 400);
        }

        $template = self::find_one_where_equal($where);

        if (!$template) {
            throw new Error('Template not found', 404);
        }

        return [
            'success' => 'Template found',
            'data' => $template,
        ];
    }

    public function update_where_equal(array $data, array $where)
    {
        $where_ = [];
        foreach ($where as $key => $value) {
            $where_[] = Raw::build("{$key} = ?", [$value]);
        }
        $where_[] = 'deleted_at IS NULL';

        $where_query = implode(' AND ', $where_);
        $data['updated_at'] = Raw::build('NOW()');
        return DB::instance()->update('mail_templates', $data, $where_query);
    }

    public function update(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $template_id = @$c->params['id'] ?? NULL;
        $code = @$c->params['code'] ?? NULL;
        $data_ = $c->json();

        $where = [
            'user_id' => $user_id,
        ];

        if ($template_id) {
            $where['id'] = $template_id;
        } else if ($code) {
            $where['code'] = $code;
        } else {
            throw new Error('Template ID or Code is required', 400);
        }

        $allowed_data = ['template', 'code', 'default', 'active'];
        $data = [];
        foreach ($allowed_data as $key) {
            if (isset($data_[$key])) {
                $data[$key] = $data_[$key];
            }
        }

        if (empty($data)) {
            throw new Error('No data to update', 400);
        }

        $old = self::find_one_where_equal($where);

        if (!$old) {
            throw new Error('Template not found', 404);
        }

        if (isset($data['code']) && !empty($data['code']) && $data['code'] !== $old['code']) {
            if (DB::instance()->query('SELECT id, user_id, code FROM mail_templates WHERE code = ? AND user_id = ? AND deleted_at IS NULL', [$data['code'], $user_id])->fetch(PDO::FETCH_ASSOC)) {
                throw new Error('Template Code already exists', 400);
            }
        } else if (isset($data['code']) && empty($data['code'])) {
            $data['code'] = NULL;
        }

        if (isset($data['default']) && !empty($data['default'])) {
            if (!is_array($data['default'])) {
                throw new Error('Default data must be an object', 400);
            }

            $data['default'] = json_encode($data['default']);
        } else if (isset($data['default']) && empty($data['default'])) {
            $data['default'] = NULL;
        }

        if (isset($data['template']) && !empty($data['template'])) {
            if (!is_string($data['template'])) {
                throw new Error('Template must be a string', 400);
            }
        } else if (isset($data['template']) && empty($data['template'])) {
            throw new Error('Template is required', 400);
        }

        if (isset($data['active']) && !empty($data['active'])) {
            $data['active'] = (is_bool($data['active']) ? $data['active'] : $data['active'] == 1) ? 1 : 0;
        }

        if (isset($data['active']) && $data['active'] == $old['active']) {
            unset($data['active']);
        }

        if (isset($data['code']) && $data['code'] === $old['code']) {
            unset($data['code']);
        }

        if (isset($data['default']) && $data['default'] === json_encode($old['default'])) {
            unset($data['default']);
        }

        if (isset($data['template']) && $data['template'] === $old['template']) {
            unset($data['template']);
        }

        if (count($data) < 1) {
            throw new Error('No data to update', 400);
        }

        $data['updated_at'] = Raw::build('NOW()');
        $affected = self::update_where_equal($data, [
            'id' => $template_id,
            'user_id' => $user_id,
        ]);

        if ($affected < 1) {
            throw new Error('Failed to update template', 500);
        }

        $template = self::find_one_where_equal([
            'id' => $old['id'],
            'user_id' => $user_id,
        ]);

        return [
            'success' => 'Template updated',
            'data' => $template,
            'debug' => [
                'data' => $data,
                'old' => $old,
            ]
        ];
    }

    public function delete_where_equal(array $where)
    {
        $where_ = [];
        foreach ($where as $key => $value) {
            $where_[] = Raw::build("{$key} = ?", [$value]);
        }
        $where_[] = 'deleted_at IS NULL';

        $where_query = implode(' AND ', $where_);
        return DB::instance()->update('mail_templates', [
            'deleted_at' => Raw::build('NOW()'),
        ], $where_query);
    }

    public function delete(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $template_id = @$c->params['id'] ?? NULL;
        $code = @$c->params['code'] ?? NULL;

        $where = [
            'user_id' => $user_id,
        ];

        if ($template_id) {
            $where['id'] = $template_id;
        } else if ($code) {
            $where['code'] = $code;
        } else {
            throw new Error('Template ID or Code is required', 400);
        }

        $old = self::find_one_where_equal($where);

        if (!$old) {
            throw new Error('Template not found', 404);
        }

        $affected = self::delete_where_equal([
            'id' => $old['id'],
            'user_id' => $user_id,
        ]);

        if ($affected < 1) {
            throw new Error('Failed to delete template', 500);
        }

        return [
            'success' => 'Template deleted',
            'data' => $old,
        ];
    }

    public function view_deleted_where_equal(array $where)
    {
        $where_ = [];
        foreach ($where as $key => $value) {
            $where_[] = Raw::build("{$key} = ?", [$value]);
        }
        $where_[] = 'deleted_at IS NOT NULL';

        $where_query = implode(' AND ', $where_);
        return DB::instance()->query('SELECT * FROM mail_templates WHERE ' . $where_query)->fetch(PDO::FETCH_ASSOC);
    }

    public function view_deleted(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $template_id = @$c->params['id'] ?? NULL;
        $code = @$c->params['code'] ?? NULL;

        $where = [
            'user_id' => $user_id,
        ];

        if ($template_id) {
            $where['id'] = $template_id;
        } else if ($code) {
            $where['code'] = $code;
        } else {
            throw new Error('Template ID or Code is required', 400);
        }

        $old = self::view_deleted_where_equal($where);

        if (!$old) {
            throw new Error('Template not found', 404);
        }

        return [
            'success' => 'Template found',
            'data' => $old,
        ];
    }

    public function restore_where_equal(array $where, array $data = [])
    {
        $where_ = [];
        foreach ($where as $key => $value) {
            $where_[] = Raw::build("{$key} = ?", [$value]);
        }
        $where_[] = 'deleted_at IS NOT NULL';

        $where_query = implode(' AND ', $where_);
        return DB::instance()->update('mail_templates', ['deleted_at' => NULL] + $data, $where_query);
    }

    public function restore(Context $c)
    {
        $user_id = @$c->user['id'] ?? 0;
        $template_id = @$c->params['id'] ?? NULL;

        $where = [
            'user_id' => $user_id,
            'id' => $template_id,
        ];

        $old = self::view_deleted_where_equal($where);

        if (!$old) {
            throw new Error('Template not found', 404);
        }

        $exists = self::find_one_where_equal([
            'code' => $old['code'],
            'user_id' => $user_id,
        ]);

        $new_code = $old['code'];

        while ($exists) {
            $exists_code = $exists['code'];
            preg_match('/-(\d+)$/', $exists_code, $matches);
            $new_code = preg_replace('/-(\d+)$/', '-' . (((int) @$matches[1]) + 1), $exists_code);
            $exists = self::find_one_where_equal([
                'code' => $new_code,
                'user_id' => $user_id,
            ]);
        }

        $data = [
            'code' => $new_code,
            'updated_at' => Raw::build('NOW()'),
        ];

        $affected = self::restore_where_equal([
            'id' => $old['id'],
            'user_id' => $user_id,
        ], $data);

        if ($affected < 1) {
            throw new Error('Failed to restore template', 500);
        }

        $template = self::find_one_where_equal([
            'id' => $old['id'],
            'user_id' => $user_id,
        ]);

        if (!$template) {
            throw new Error('Template restored but not found', 500);
        }

        return [
            'success' => 'Template restored',
            'data' => $template,
            'debug' => [
                'data' => $template,
                'old' => $old,
            ]
        ];
    }
}
