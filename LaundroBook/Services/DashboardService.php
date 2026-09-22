<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

/*
  DashboardService

  Pulls together the numbers the admin dashboard needs from Booking,
  Machine and Delivery data. AdminController used to have a commented
  out dashboard() method that only called
  $this->bookingService->getTodaysBookings() - everything else on the
  page (pending bookings, pickups, deliveries, available machines,
  revenue, notifications) had nothing behind it at all. This is the
  one place that gathers all of it, the same way BookingService is the
  one place a booking gets created, so AdminController just calls this
  and hands the result to the view.
*/
class DashboardService
{
    private BookingRepoInterface $bookingRepo;
    private MachineRepoInterface $machineRepo;
    private DeliveryRepoInterface $deliveryRepo;

    public function __construct(
        BookingRepoInterface $bookingRepo,
        MachineRepoInterface $machineRepo,
        DeliveryRepoInterface $deliveryRepo
    ) {
        $this->bookingRepo = $bookingRepo;
        $this->machineRepo = $machineRepo;
        $this->deliveryRepo = $deliveryRepo;
    }

    // One call for every stat card on the dashboard.
    public function getStats(): array
    {
        return [
            'today_bookings' => $this->bookingRepo->todaysBookings(),
            'pending_bookings' => $this->bookingRepo->pendingBookingsCount(),
            'today_pickups' => $this->deliveryRepo->todaysCountByType('collection'),
            'today_deliveries' => $this->deliveryRepo->todaysCountByType('delivery'),
            'available_machines' => $this->machineRepo->countByStatus('available'),
            'today_revenue' => $this->bookingRepo->todaysRevenue(),
        ];
    }

    // Builds the list of cards shown in "System Notifications".
    // Each notification is [type, message, link] so the view can pick
    // an icon per type and link straight to the relevant management
    // page. Returns an empty array when there is nothing to flag,
    // the view is responsible for showing "no notifications" in that case.
    public function getNotifications(): array
    {
        $notifications = [];

        $pending = $this->bookingRepo->pendingBookingsCount();
        if ($pending > 0) {
            $notifications[] = [
                'type' => 'booking',
                'message' => $pending === 1
                    ? '1 booking is awaiting approval.'
                    : "{$pending} bookings are awaiting approval.",
                'link' => 'bookingManagement.php?status=pending',
            ];
        }

        $underMaintenance = $this->machineRepo->countByStatus('under_maintenance');
        if ($underMaintenance > 0) {
            $notifications[] = [
                'type' => 'machine',
                'message' => $underMaintenance === 1
                    ? '1 machine is under maintenance.'
                    : "{$underMaintenance} machines are under maintenance.",
                'link' => 'machineManagement.php?status=under_maintenance',
            ];
        }

        $pendingPickups = count($this->deliveryRepo->getAll([
            'type' => 'collection',
            'status' => 'pending',
        ]));
        if ($pendingPickups > 0) {
            $notifications[] = [
                'type' => 'pickup',
                'message' => $pendingPickups === 1
                    ? '1 pickup needs attention.'
                    : "{$pendingPickups} pickups need attention.",
                'link' => 'pickupManagement.php?status=pending',
            ];
        }

        $pendingDeliveries = count($this->deliveryRepo->getAll([
            'type' => 'delivery',
            'status' => 'pending',
        ]));
        if ($pendingDeliveries > 0) {
            $notifications[] = [
                'type' => 'delivery',
                'message' => $pendingDeliveries === 1
                    ? '1 delivery needs attention.'
                    : "{$pendingDeliveries} deliveries need attention.",
                'link' => 'deliveryManagement.php?status=pending',
            ];
        }

        return $notifications;
    }
}
