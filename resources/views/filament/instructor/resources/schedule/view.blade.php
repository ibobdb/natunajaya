<div class="space-y-4 p-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Basic Info Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Schedule Information</h3>

            <div class="grid grid-cols-1 gap-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule ID</p>
                    <p class="text-base">{{ $record->id }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Course</p>
                    <p class="text-base">{{ $record->studentCourse->course->name ?? 'Not assigned' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Student</p>
                    <p class="text-base">{{ $record->studentCourse->student->name ?? 'Not assigned' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Session</p>
                    <p class="text-base">{{ $record->for_session ?? 'Not set' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                    <div>
                        @php
                        $color = match($record->status) {
                        'waiting_approval' => 'bg-yellow-100 text-yellow-800',
                        'waiting_instructor_approval' => 'bg-blue-100 text-blue-800',
                        'waiting_admin_approval' => 'bg-gray-100 text-gray-800',
                        'date_not_set' => 'bg-red-100 text-red-800',
                        'ready' => 'bg-green-100 text-green-800',
                        'waiting_signature' => 'bg-yellow-100 text-yellow-800',
                        'complete' => 'bg-green-100 text-green-800',
                        default => 'bg-gray-100 text-gray-800',
                        };
                        $status = str_replace('_', ' ', ucfirst($record->status));
                        @endphp
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date and Staff Info -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Session Details</h3>

            <div class="grid grid-cols-1 gap-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</p>
                    <p class="text-base">
                        {{ $record->start_date ? $record->start_date->format('M d, Y H:i') : 'Date not set' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Car</p>
                    <p class="text-base">{{ $record->car->name ?? 'Car not set' }}</p>
                </div>

                @if($record->notes)
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</p>
                    <p class="text-base">{{ $record->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Signature Status -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Signature Status</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center">
                <span
                    class="h-8 w-8 rounded-full flex items-center justify-center mr-3 {{ $record->att_student ? 'bg-green-100' : 'bg-red-100' }}">
                    @if($record->att_student)
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    @endif
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Student Signature</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $record->att_student ? 'Signed' : 'Not signed' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center">
                <span
                    class="h-8 w-8 rounded-full flex items-center justify-center mr-3 {{ $record->att_instructor ? 'bg-green-100' : 'bg-red-100' }}">
                    @if($record->att_instructor)
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    @endif
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Instructor Signature</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $record->att_instructor ? 'Signed' : 'Not signed' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>