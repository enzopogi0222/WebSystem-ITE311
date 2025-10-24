<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\NotificationModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Array of helpers to load automatically upon instantiation.
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Session instance
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Initialize controller
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Load session service
        $this->session = service('session');

        // You can preload models/libraries here if needed
        // Example: $this->userModel = new \App\Models\UserModel();
    }

    /**
     * Loads notifications for the current logged-in user.
     *
     * @return array
     */
    protected function loadNotifications(): array
    {
        $userId = $this->session->get('user_id');

        if ($userId) {
            $notificationModel = new NotificationModel();
            $unreadCount = $notificationModel->getUnreadCount($userId);
            $notifications = $notificationModel->getNotificationsForUser($userId);

            return [
                'unreadCount'   => $unreadCount,
                'notifications' => $notifications,
            ];
        }

        return [
            'unreadCount'   => 0,
            'notifications' => [],
        ];
    }
}
