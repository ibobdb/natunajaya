<div class="space-y-4">
    @foreach($schedules as $schedule)
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex justify-between items-start">
                <div>
              @if($schedule->start_date)
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  
                      {{ \Carbon\Carbon::parse($schedule->start_date)->format('l, d F Y') }}
              
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">        
                  {{ \Carbon\Carbon::parse($schedule->start_date)->format('H:i') }}
                </p>
              @else
                  <span class="text-red-500">Date not set</span>
              @endif
                @if($schedule->for_session)
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                  Session: {{ $schedule->for_session }}
                  </p>
                @endif
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                    match($schedule->status) {
                        'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                        'ready' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                        'waiting_approval' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        'waiting_instructor_approval' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        'waiting_admin_approval' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        default => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    }
                        
                }}">
                
                    {{ ucfirst($schedule->status) }}
                </span>
            </div>
            
            @if($schedule->location)
                <div class="mt-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Location:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $schedule->location }}</p>
                </div>
            @endif
            
            @if($schedule->notes)
                <div class="mt-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $schedule->notes }}</p>
                </div>
            @endif
        </div>
    @endforeach
</div>
