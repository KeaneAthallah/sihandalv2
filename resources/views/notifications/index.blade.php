<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifikasi" :breadcrumbs="['Notifikasi']">
            <x-slot name="actions">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                        @csrf
                        <x-secondary-button type="submit" class="gap-2">
                            <x-heroicon-o-check-circle class="w-4 h-4"/>
                            Tandai Semua Sudah Dibaca
                        </x-secondary-button>
                    </form>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    @if($unreadCount > 0)
        <x-alert type="info" :dismissible="false">
            Anda memiliki <span class="font-bold">{{ $unreadCount }}</span> notifikasi belum dibaca.
        </x-alert>
    @endif

    <div class="max-w-4xl">
        <x-card padding="false">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <div class="px-5 lg:px-6 py-4 transition-colors {{ $isUnread ? 'bg-primary/[0.03]' : 'hover:bg-slate-50/50' }}">
                        <div class="flex items-start gap-4">
                            <div class="mt-0.5 shrink-0">
                                @if($isUnread)
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10">
                                        <x-heroicon-o-bell class="w-4 h-4 text-primary" />
                                    </div>
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100">
                                        <x-heroicon-o-check-circle class="w-4 h-4 text-slate-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-semibold {{ $isUnread ? 'text-slate-900' : 'text-slate-600' }}">
                                        {{ $data['title'] ?? '-' }}
                                    </h4>
                                    @if($isUnread)
                                        <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary uppercase tracking-wider">Baru</span>
                                    @endif
                                </div>
                                <p class="text-sm {{ $isUnread ? 'text-slate-600' : 'text-slate-400' }} mt-1 leading-relaxed">{{ $data['message'] ?? '-' }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    @if(isset($data['link']))
                                        <a href="{{ $data['link'] }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-dark transition-colors">
                                            Lihat Detail
                                            <x-heroicon-o-arrow-right class="w-3 h-3" />
                                        </a>
                                    @endif
                                    <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            @if($isUnread)
                                <form method="POST" action="{{ route('notifications.markAsRead', $notification) }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Tandai sudah dibaca">
                                        <x-heroicon-o-check class="w-4 h-4"/>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                <x-heroicon-o-bell-slash class="h-7 w-7 text-slate-300"/>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Belum ada notifikasi</p>
                                <p class="text-xs text-slate-400 mt-1">Notifikasi akan muncul di sini</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-5 lg:px-6 py-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
