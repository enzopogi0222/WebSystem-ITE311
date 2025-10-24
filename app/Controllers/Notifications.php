<?php

namespace App\Controllers;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new \App\Models\NotificationModel();
    }

    public function get()
    {
        $userId = session()->get('userID');
        if (!$userId) {
            return $this->response->setJSON([
                'count' => 0,
                'notifications' => []
            ]);
        }

        $notifications = $this->notificationModel->getNotificationsForUser($userId);
        $count = $this->notificationModel->getUnreadCount($userId);

    return $this->response->setJSON([
    'count' => count($notifications),
    'notifications' => $notifications
    ]);

        
    }

    public function mark_as_read($id)
    {
        $success = $this->notificationModel->markAsRead($id);
        return $this->response->setJSON(['success' => $success]);
    }
}