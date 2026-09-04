<?php
declare(strict_types=1);

namespace App;

class Config
{
    public const APP_NAME = "UniPano";
    public const APP_TAGLINE = "Üniversite Dijital Kampüs Panosu";
    public const APP_VERSION = "2.0.0";

    public const ROOT_PATH = __DIR__ . "/../";
    public const PATH_TO_SQLITE_FILE = __DIR__ . '/../db/phpsqlite.db';

    public const SESSION_AUTH_KEY = "unipano_user_id";
    public const SESSION_CSRF_KEY = "unipano_csrf_token";
    public const LOGIN_COOKIE_NAME = "unipano_session";

    public const UPLOAD_DIR = __DIR__ . "/../images/";
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const MAX_IMAGE_SIZE_BYTES = 10 * 1024 * 1024; // 10MB
}