<!-- Toast Container -->
<div id="toast-container" class="fixed top-6 right-6 z-[100] space-y-3 max-w-md">
    <!-- Toasts will be dynamically inserted here -->
</div>

@once
@push('scripts')
<script>
function showToast(message, type = 'info', duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const config = {
        success: {
            bg: 'bg-emerald-500/10',
            border: 'border-emerald-400/30',
            text: 'text-emerald-400',
            icon: 'check-circle',
        },
        error: {
            bg: 'bg-rose-500/10',
            border: 'border-rose-400/30',
            text: 'text-rose-400',
            icon: 'x-circle',
        },
        warning: {
            bg: 'bg-amber-500/10',
            border: 'border-amber-400/30',
            text: 'text-amber-400',
            icon: 'alert-triangle',
        },
        info: {
            bg: 'bg-cyan-500/10',
            border: 'border-cyan-400/30',
            text: 'text-cyan-400',
            icon: 'info',
        },
    };
    
    const cfg = config[type] || config.info;
    const toastId = 'toast-' + Date.now();
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `glass-strong ${cfg.bg} border ${cfg.border} rounded-xl p-4 flex items-start gap-3 shadow-2xl animate-slide-in-right`;
    toast.innerHTML = `
        <i data-lucide="${cfg.icon}" class="w-5 h-5 ${cfg.text} flex-shrink-0 mt-0.5"></i>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-white font-medium">${message}</p>
        </div>
        <button 
            onclick="removeToast('${toastId}')"
            class="w-6 h-6 rounded-lg flex items-center justify-center hover:bg-white/5 transition-colors flex-shrink-0"
        >
            <i data-lucide="x" class="w-4 h-4 text-cyan-100/60"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Initialize Lucide icons for new toast
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            removeToast(toastId);
        }, duration);
    }
}

function removeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('animate-slide-out-right');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Auto-show toasts from Laravel session
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
    
    @if(session('warning'))
        showToast('{{ session('warning') }}', 'warning');
    @endif
    
    @if(session('info'))
        showToast('{{ session('info') }}', 'info');
    @endif
});
</script>

<style>
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.animate-slide-in-right {
    animation: slideInRight 0.3s ease-out forwards;
}

.animate-slide-out-right {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>
@endpush
@endonce
