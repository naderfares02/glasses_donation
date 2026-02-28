<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    private function isStaff(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /**
     * عرض قائمة الشكاوى
     * - staff يشوف الكل
     * - غيرهم: فقط شكاواهم أو شكاوي عليهم
     */
    public function viewAny(User $user): bool
    {
        return true; // رح تعمل فلترة في الـController حسب الدور
    }

    /**
     * عرض شكوى محددة
     */
    public function view(User $user, Complaint $complaint): bool
    {
        if ($this->isStaff($user)) return true;

        return $complaint->reporter_id === $user->id
            || $complaint->reported_user_id === $user->id;
    }

    /**
     * إنشاء شكوى
     * - المستخدم لازم يكون طرف بالمحادثة
     * - ما ينفع يشتكي على نفسه
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['donor', 'recipient'], true);
    }

    /**
     * تحديث الشكوى (نادرًا)
     * - فقط staff أو صاحب الشكوى إذا كانت open (مثلاً تعديل الوصف فقط)
     */
    public function update(User $user, Complaint $complaint): bool
    {
        if ($this->isStaff($user)) return true;

        return $complaint->reporter_id === $user->id
            && $complaint->status === 'open';
    }

    /**
     * تغيير الحالة / قرار الشكوى
     * - staff فقط
     */
    public function changeStatus(User $user, Complaint $complaint): bool
    {
        return $this->isStaff($user);
    }

    /**
     * إرسال رسالة داخل الشكوى
     * - staff دائمًا
     * - reporter دائمًا طالما مش resolved/dismissed (اختياري)
     * - reported_user (اختياري) إذا بدك تسمح له يرد
     */
    public function sendMessage(User $user, Complaint $complaint): bool
    {
        if ($this->isStaff($user)) return true;

        $isParticipant = $complaint->reporter_id === $user->id
            || $complaint->reported_user_id === $user->id;

        if (!$isParticipant) return false;

        // منع الرسائل بعد الإغلاق (اختياري)
        return in_array($complaint->status, ['open', 'reviewing'], true);
    }

    /**
     * حذف الشكوى
     * - super_admin فقط
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->role === 'super_admin';
    }
}