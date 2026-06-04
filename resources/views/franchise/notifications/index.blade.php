@extends('layouts.franchise')
@section('title', 'Notification Center')
@section('page-title', 'Notification Center')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Compose --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6" x-data="notificationForm()">
            <h2 class="text-sm font-bold text-fran mb-4">Compose Notification</h2>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('franchise.notifications.send') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Template</label>
                        <select x-model="tpl" @change="applyTemplate()"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">— Select a template —</option>
                            <option value="exam_result">Exam Result</option>
                            <option value="fee_reminder">Fee Reminder</option>
                            <option value="exam_schedule">Exam Schedule</option>
                            <option value="class_cancelled">Class Cancelled</option>
                            <option value="achievement">Achievement</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Send To</label>
                        <select name="target" x-model="target" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="all">All Students</option>
                            <option value="level">By Level</option>
                            <option value="student">Individual</option>
                        </select>
                    </div>
                    <div x-show="target === 'level'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level</label>
                        <select name="level_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>Level {{ $level->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="target === 'student'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Student</label>
                        <select name="student_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Student</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Message Preview --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Message Preview</label>
                        <div class="bg-bg-light rounded-xl p-3 border border-border min-h-[80px] text-xs text-gray-700 leading-relaxed" x-text="message || 'Select a template or type a message below...'"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="3" x-model="message" required maxlength="500"
                                  placeholder="Type your message here..."
                                  class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none"></textarea>
                        <input type="hidden" name="title" :value="title || 'Notification'">
                    </div>

                    {{-- Recipient count --}}
                    <p class="text-xs text-gray-500">
                        Recipients: <span class="font-semibold text-fran" x-text="recipientCount"></span> students
                    </p>

                    {{-- Two send buttons --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="submit" name="channel" value="whatsapp"
                                class="py-2.5 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark transition-colors">
                            Send WhatsApp (<span x-text="recipientCount"></span>)
                        </button>
                        <button type="submit" name="channel" value="sms"
                                class="py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                            Send SMS (<span x-text="recipientCount"></span>)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- History --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">Notification History</h2>
        </div>
        <div class="divide-y divide-border">
            @forelse($history as $notif)
                <div class="px-5 py-4 hover:bg-bg-light">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $notif->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $notif->message }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notif->created_at->format('d M Y, H:i') }}
                                · {{ ucfirst($notif->channel) }}
                                @if($notif->student)
                                    · to {{ $notif->student->full_name }}
                                @endif
                            </p>
                        </div>
                        <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium flex-shrink-0">Delivered</span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    No notifications sent yet. Use the form to send your first message.
                </div>
            @endforelse
        </div>
        @if($history->hasPages())
            <div class="px-5 py-4 border-t border-border">
                {{ $history->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

{{-- Alpine component for the compose form. Defined in a <script> (not inline in
     x-data) so @json values keep their double quotes without breaking the
     x-data="..." attribute. Edit the template messages below to change them. --}}
<script>
    function notificationForm() {
        return {
            target: @json(old('target', 'all')),
            title: @json(old('title', '')),
            message: @json(old('message', '')),
            tpl: '',
            totalStudents: {{ $totalStudents }},
            templates: {
                exam_result:     { title: 'Exam Results Published',   message: "Dear Parent, your child's exam results are now available. Please log in to the student portal to view the detailed results and performance report." },
                fee_reminder:    { title: 'Fee Payment Reminder',     message: "This is a friendly reminder that the monthly fee for your child is due. Please make the payment at the earliest to avoid any inconvenience." },
                exam_schedule:   { title: 'Upcoming Exam Schedule',   message: "Please be informed that the next level exam is scheduled. Ensure your child is prepared and arrives on time. Further details will be shared soon." },
                class_cancelled: { title: 'Class Cancelled',          message: "Please note that today's class has been cancelled due to unavoidable circumstances. We apologise for the inconvenience. Classes will resume as scheduled." },
                achievement:     { title: 'Achievement Unlocked! 🏆', message: "Congratulations! Your child has achieved a milestone in their abacus journey. We are proud of their dedication and hard work. Keep it up!" },
            },
            get recipientCount() {
                return this.target === 'all' ? this.totalStudents : (this.target === 'student' ? 1 : Math.ceil(this.totalStudents / 7));
            },
            applyTemplate() {
                if (this.tpl && this.templates[this.tpl]) {
                    this.title   = this.templates[this.tpl].title;
                    this.message = this.templates[this.tpl].message;
                }
            },
        };
    }
</script>

@endsection
