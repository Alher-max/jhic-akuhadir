@props(['id', 'title', 'message', 'confirmButtonText' => 'Ya, Lanjutkan', 'cancelButtonText' => 'Batal', 'icon' => 'warning'])

<div x-data="{ show: false, form: null }"
    @open-confirm-modal.window="show = true; form = $event.detail.form; $el.querySelector('#modal-title').innerText = $event.detail.title; $el.querySelector('#modal-message').innerText = $event.detail.message;"
    x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>

    <div @click.away="show = false" class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
        <!-- Icon -->
        <div class="mx-auto mb-4 flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
            @if($icon == 'warning')
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-3xl"></i>
            @elseif($icon == 'trash')
                <i class="fa-solid fa-trash text-red-600 text-3xl"></i>
            @else
                <i class="fa-solid fa-circle-exclamation text-yellow-600 text-3xl"></i>
            @endif
        </div>

        <!-- Title & Subtitle -->
        <h3 id="modal-title" class="text-xl font-bold text-gray-900 mb-2"></h3>
        <p id="modal-message" class="text-gray-600 mb-6"></p>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button @click="show = false"
                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                {{ $cancelButtonText }}
            </button>
            <button @click="form.submit(); show = false;"
                class="flex-1 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                {{ $confirmButtonText }}
            </button>
        </div>
    </div>
</div>