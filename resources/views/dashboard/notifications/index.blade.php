<x-sidebar-layout>
    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Notifications' => null]" />
    </x-slot>

    <div class="py-12 px-4 sm:px-4 md:px-4 lg:px-0">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            @if(auth()->user()->unreadNotifications->count() > 0)
                <div class="mb-4 flex justify-end">
                    <form action="{{ route('dashboard.notifications.markAllAsRead') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @forelse ($notifications as $notification)
                    <div class="p-4 border-b border-gray-200 hover:bg-gray-50 transition">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-gray-900 mb-1">
                            {{ $notification->data['title'] }}
                        </h3>
                        <p class="text-sm sm:text-base md:text-lg text-gray-700 mb-2">
                            {{ $notification->data['message'] }}
                        </p>
                        <span class="text-xs sm:text-sm text-gray-500">
                            {{ $notification->created_at->diffForHumans() }} -
                        </span>
                        <span class="text-xs sm:text-sm text-gray-500">
                            {{ $notification->created_at->format('Y-m-d H:i:s') }}
                        </span>
                        @if ($notification->read_at)
                            <span class="text-xs sm:text-sm text-green-500 ml-2">
                                (Read)
                            </span>
                        @else
                            <span class="text-xs sm:text-sm text-red-500 ml-2">
                                (Unread)
                            </span>
                        @endif

                        @if ($notification->type === \App\Notifications\WorkProgramCommentNotification::class)
                            <div class="mt-2">
                                <a href="{{ $notification->data['url'] }}"
                                    class="text-blue-500 hover:underline text-sm sm:text-base">
                                    Lihat Komentar
                                </a>
                            </div>
                        @endif
                        @if (is_null($notification->read_at))
                            <div class="mt-2">
                                <form
                                    action="{{ route('dashboard.notifications.markAsRead', ['id' => $notification->id]) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-blue-500 hover:underline text-sm sm:text-base">
                                        Mark as Read
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                @empty
                    <div class="p-6 text-gray-900 text-lg sm:text-xl">
                        No notifications available.
                    </div>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-sidebar-layout>
