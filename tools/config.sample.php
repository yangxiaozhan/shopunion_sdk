<?php

/**
 * 调试工具配置示例
 * 复制为 config.local.php 并填写真实配置，config.local.php 已在 .gitignore 中
 *
 * 或通过环境变量设置（推荐）：
 *   TAOBAO_APP_KEY, TAOBAO_APP_SECRET, TAOBAO_PID, TAOBAO_ADZONE_ID, TAOBAO_SITE_ID
 *   PDD_CLIENT_ID, PDD_CLIENT_SECRET, PDD_PID
 *   JD_APP_KEY, JD_APP_SECRET, JD_UNION_ID, JD_POSITION_ID
 */
return [
    'taobao' => [
        'app_key'         => getenv('TAOBAO_APP_KEY') ?: '',
        'app_secret'      => getenv('TAOBAO_APP_SECRET') ?: '',
        'pid'             => getenv('TAOBAO_PID') ?: '',
        'adzone_id'       => getenv('TAOBAO_ADZONE_ID') ?: '',
        'site_id'         => getenv('TAOBAO_SITE_ID') ?: '',
        'session'         => getenv('TAOBAO_SESSION') ?: '',
        // 'connect_timeout' => 15,  // 连接超时秒，遇“远程服务调用超时”可适当增大
        // 'read_timeout'    => 60,  // 读取超时秒
    ],
    'pinduoduo' => [
        'client_id'     => getenv('PDD_CLIENT_ID') ?: '',
        'client_secret' => getenv('PDD_CLIENT_SECRET') ?: '',
        'pid'           => getenv('PDD_PID') ?: '',
    ],
    'jd' => [
        'app_key'     => getenv('JD_APP_KEY') ?: '',
        'app_secret'  => getenv('JD_APP_SECRET') ?: '',
        'union_id'    => getenv('JD_UNION_ID') ?: '',
        'position_id' => getenv('JD_POSITION_ID') ?: '',
    ],
];
