@extends('layouts.app')

@section('title', 'Daily Time Record')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Clock In/Out Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Time Record - {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y - h:i A') }}</h2>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="text-center">
                <form id="clockInForm" action="{{ route('dtr.clock-in') }}" method="POST">
                    @csrf
                    <input type="hidden" name="face_descriptor" id="clockin_face_descriptor">
                    <button id="clockInBtn" type="submit" 
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 w-full 
                            {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) ? 'disabled' : '' }}>
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Clock In
                    </button>
                    @if($dtrRecord)
                        @if($dtrRecord->time_in)
                            <p class="mt-2 text-gray-600">{{ \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($dtrRecord->time_in)) }} Clock In: {{ \Carbon\Carbon::parse($dtrRecord->time_in)->format('h:i A') }}</p>
                        @endif
                        @if($dtrRecord->time_in_2)
                            <p class="mt-2 text-gray-600">{{ \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($dtrRecord->time_in_2)) }} Clock In: {{ \Carbon\Carbon::parse($dtrRecord->time_in_2)->format('h:i A') }}</p>
                        @endif
                    @endif
                </form>
            </div>

            <div class="flex items-center justify-center md:justify-start md:col-span-2">
                @php $currentUser = auth()->user(); @endphp
                @if($currentUser && empty($currentUser->face_embedding))
                    <a href="{{ route('face.register') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded shadow hover:bg-yellow-600">
                        <i class="fas fa-user-plus mr-2"></i> Register Face (one-time)
                    </a>
                    <p class="ml-4 text-sm text-gray-600">You must register your face once before using face verification for clock in/out.</p>
                @else
                    <p class="text-sm text-gray-600">Face registered: {{ $currentUser && $currentUser->face_embedding ? 'Yes' : 'No' }}</p>
                @endif
            </div>

            <div class="text-center">
                <form id="clockOutForm" action="{{ route('dtr.clock-out') }}" method="POST">
                    @csrf
                    <input type="hidden" name="face_descriptor" id="clockout_face_descriptor">
                    <button id="clockOutBtn" type="submit" 
                        class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 w-full 
                            {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) ? 'disabled' : '' }}>
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Clock Out
                    </button>
                    @if($dtrRecord)
                        @if($dtrRecord->time_out)
                            <p class="mt-2 text-gray-600">{{ \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($dtrRecord->time_out)) }} Clock Out: {{ \Carbon\Carbon::parse($dtrRecord->time_out)->format('h:i A') }}</p>
                        @endif
                        @if($dtrRecord->time_out_2)
                            <p class="mt-2 text-gray-600">{{ \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($dtrRecord->time_out_2)) }} Clock Out: {{ \Carbon\Carbon::parse($dtrRecord->time_out_2)->format('h:i A') }}</p>
                        @endif
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Monthly Records Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">Monthly Records - {{ now()->format('F Y') }}</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Time In 1</th>
                        <th class="px-4 py-2">Time Out 1</th>
                        <th class="px-4 py-2">Time In 2</th>
                        <th class="px-4 py-2">Time Out 2</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Work Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRecords as $record)
                        <tr class="border-b">
                            <td class="px-4 py-2 text-center">{{ $record->date->format('M d, Y') }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_in ? \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($record->time_in)) . ': ' . \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_out ? \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($record->time_out)) . ': ' . \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_in_2 ? \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($record->time_in_2)) . ': ' . \Carbon\Carbon::parse($record->time_in_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $record->time_out_2 ? \App\Helpers\TimeHelper::getTimeOfDay(\Carbon\Carbon::parse($record->time_out_2)) . ': ' . \Carbon\Carbon::parse($record->time_out_2)->format('h:i A') : '-' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">{{ round($record->work_hours, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal for camera preview and capture -->
<div id="faceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-lg">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold">Face Verification</h3>
            <button id="modalCloseBtn" class="text-gray-600 hover:text-gray-900">✕</button>
        </div>
        <div class="mb-2">
            <video id="modalVideo" width="100%" height="320" autoplay muted playsinline class="bg-gray-100 rounded"></video>
        </div>
        <div id="modalStatus" class="text-sm text-gray-600 mb-3">Preparing camera...</div>
        <div class="flex gap-2 justify-end">
            <button id="modalCaptureBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Capture & Verify</button>
            <button id="modalCancelBtn" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
// Load models and provide modal camera UI for capture
async function loadFaceModels() {
    const local = '/models';
    const cdn = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

    async function tryLoad(url) {
        await faceapi.nets.tinyFaceDetector.loadFromUri(url);
        await faceapi.nets.faceLandmark68Net.loadFromUri(url);
        await faceapi.nets.faceRecognitionNet.loadFromUri(url);
    }

    try {
        await tryLoad(local);
        console.log('Loaded face models from', local);
        return;
    } catch (e) {
        console.warn('Local face models not found at', local);
    }

    try {
        await tryLoad(cdn);
        console.log('Loaded face models from CDN', cdn);
        return;
    } catch (e) {
        console.error('Failed to load face models from CDN', cdn, e);
        throw new Error('Face models could not be loaded. Place models under /public/models or ensure CDN is reachable.');
    }
}

// Modal elements
const faceModal = document.getElementById('faceModal');
const modalVideo = document.getElementById('modalVideo');
const modalStatus = document.getElementById('modalStatus');
const modalCaptureBtn = document.getElementById('modalCaptureBtn');
const modalCancelBtn = document.getElementById('modalCancelBtn');
const modalCloseBtn = document.getElementById('modalCloseBtn');

let modalStream = null;
let currentFormId = null;
let currentInputId = null;
let currentButton = null;

function openModal(formId, inputId, triggerBtn) {
    currentFormId = formId;
    currentInputId = inputId;
    currentButton = triggerBtn;
    faceModal.classList.remove('hidden');
    faceModal.classList.add('flex');
    modalStatus.textContent = 'Preparing camera...';
    startModalCamera();
}

async function closeModal() {
    faceModal.classList.add('hidden');
    faceModal.classList.remove('flex');
    stopModalCamera();
    modalStatus.textContent = '';
    currentFormId = null;
    currentInputId = null;
    currentButton = null;
}

async function startModalCamera() {
    try {
        modalStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        modalVideo.srcObject = modalStream;
        await modalVideo.play();
        modalStatus.textContent = 'Camera ready. Captures will average 3 samples.';
    } catch (err) {
        modalStatus.textContent = 'Cannot access camera: ' + err.message;
    }
}

function stopModalCamera() {
    if (modalStream) {
        modalStream.getTracks().forEach(t => t.stop());
        modalStream = null;
    }
    try { modalVideo.pause(); modalVideo.srcObject = null; } catch(e){}
}

function avgDescriptors(descriptors) {
    if (!descriptors.length) return [];
    const len = descriptors[0].length;
    const out = new Array(len).fill(0);
    descriptors.forEach(d => {
        for (let i = 0; i < len; i++) out[i] += d[i];
    });
    for (let i = 0; i < len; i++) out[i] = out[i] / descriptors.length;
    return out;
}

modalCaptureBtn.addEventListener('click', async function () {
    modalCaptureBtn.disabled = true;
    modalStatus.textContent = 'Capturing 3 samples — please slowly move your head between captures.';
    try {
        const descriptors = [];
        for (let i = 0; i < 3; i++) {
            modalStatus.textContent = `Capturing sample ${i+1} of 3...`;
            const detection = await faceapi.detectSingleFace(modalVideo, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!detection) throw new Error('No face detected for sample ' + (i+1));
            descriptors.push(Array.from(detection.descriptor));
            await new Promise(res => setTimeout(res, 600));
        }
        const averaged = avgDescriptors(descriptors);
        document.getElementById(currentInputId).value = JSON.stringify(averaged);
        document.getElementById(currentFormId).submit();
    } catch (err) {
        modalStatus.textContent = 'Capture failed: ' + err.message;
        console.error(err);
        modalCaptureBtn.disabled = false;
    }
});

modalCancelBtn.addEventListener('click', closeModal);
modalCloseBtn.addEventListener('click', closeModal);

document.addEventListener('DOMContentLoaded', function () {
    loadFaceModels().catch(err => console.warn('Face models failed to load', err));

    const clockInBtn = document.getElementById('clockInBtn');
    if (clockInBtn) {
        clockInBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal('clockInForm', 'clockin_face_descriptor', clockInBtn);
        });
    }

    const clockOutBtn = document.getElementById('clockOutBtn');
    if (clockOutBtn) {
        clockOutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal('clockOutForm', 'clockout_face_descriptor', clockOutBtn);
        });
    }
});
</script>
@endpush

@endsection