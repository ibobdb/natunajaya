<div class="flex justify-center space-x-4 py-2">
    <button wire:click="filterCurrentMonth" class="{{ $currentMonthClass }}">
        Current Month
    </button>
    <button wire:click="filterPreviousMonth" class="{{ $previousMonthClass }}">
        Previous Month
    </button>
    <button wire:click="filterLast30Days" class="{{ $last30DaysClass }}">
        Last 30 Days
    </button>
</div>