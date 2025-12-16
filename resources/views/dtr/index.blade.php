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
                            {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) || $onLeave ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ ($dtrRecord && $dtrRecord->time_in && $dtrRecord->time_out && $dtrRecord->time_in_2) || $onLeave ? 'disabled' : '' }}>
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

            <div class="text-center">
                <form id="clockOutForm" action="{{ route('dtr.clock-out') }}" method="POST">
                    @csrf
                    <input type="hidden" name="face_descriptor" id="clockout_face_descriptor">
                    <button id="clockOutBtn" type="submit" 
                        class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 w-full 
                            {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) || $onLeave ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ (!$dtrRecord || (!$dtrRecord->time_in && !$dtrRecord->time_in_2) || ($dtrRecord->time_out && $dtrRecord->time_out_2)) || $onLeave ? 'disabled' : '' }}>
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

        <div class="mt-6 text-center">
            @php $currentUser = auth()->user(); @endphp
            @if($currentUser && empty($currentUser->face_embedding))
                <a href="{{ route('face.register') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded shadow hover:bg-yellow-600">
                    <i class="fas fa-user-plus mr-2"></i> Register Face (one-time)
                </a>
                <p class="mt-2 text-sm text-gray-600">You must register your face once before using face verification for clock in/out.</p>
            @else
                <p class="text-sm text-gray-600">Face registered: {{ $currentUser && $currentUser->face_embedding ? 'Yes' : 'No' }}</p>
            @endif
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
                        <th class="px-4 py-2">Regular Work Hours</th>
                        <th class="px-4 py-2">Overtime Hours</th>
                        <th class="px-4 py-2">Total Work Hours</th>
                        <th class="px-4 py-2">Status</th>
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
                            <td class="px-4 py-2 text-center">{{ $record->formatted_regular_work_hours }}</td>
                            <td class="px-4 py-2 text-center">{{ round($record->overtime_hours, 2) }}</td>
                            <td class="px-4 py-2 text-center">{{ round($record->total_work_hours, 2) }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded-full text-xs capitalize
                                    {{ $record->status === 'present' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $record->status === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $record->status === 'absent' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-2 text-center text-gray-500">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal for camera preview and capture -->
<div id="faceModal" class="fixed inset-0 bg-transparent hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-lg">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold">Face Verification</h3>
            <button id="modalCloseBtn" class="text-gray-600 hover:text-gray-900">✕</button>
        </div>
        <div class="mb-2">
            <div style="position:relative; width:100%; height:320px;">
                <video id="modalVideo" width="100%" height="320" autoplay muted playsinline class="bg-gray-100 rounded"></video>
                <canvas id="modalOverlay" style="position:absolute; left:0; top:0; width:100%; height:320px; pointer-events:none;"></canvas>
            </div>
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
const modalOverlay = document.getElementById('modalOverlay');
let modalOverlayCtx = null;

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
        if (modalOverlay) {
            modalOverlayCtx = modalOverlay.getContext('2d');
            // set canvas size based on video
            modalOverlay.width = modalVideo.videoWidth || modalVideo.clientWidth;
            modalOverlay.height = modalVideo.videoHeight || modalVideo.clientHeight;
        }
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
    modalStatus.textContent = 'Checking face stability...';
    try {
        const start = Date.now();
        const timeout = 7000;
        const NEED_CONSECUTIVE = 3;
        let consecutive = 0;

        function getHeadNormalized(landmarks, box){ const nose = landmarks.getNose()[3]; const leftEye = landmarks.getLeftEye(); const rightEye = landmarks.getRightEye(); const eyeCx = (leftEye.reduce((s,p)=>s+p.x,0)/leftEye.length + rightEye.reduce((s,p)=>s+p.x,0)/rightEye.length)/2; return (nose.x - eyeCx) / box.width; }

        while (Date.now() - start < timeout) {
            const detection = await faceapi.detectSingleFace(modalVideo, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!detection) {
                consecutive = 0;
                if (modalOverlayCtx) modalOverlayCtx.clearRect(0,0,modalOverlay.width, modalOverlay.height);
                await new Promise(r=>setTimeout(r,80));
                continue;
            }

            const head = getHeadNormalized(detection.landmarks, detection.detection.box);
            // draw overlay + landmark mesh
            if (modalOverlayCtx) {
                modalOverlayCtx.clearRect(0,0,modalOverlay.width, modalOverlay.height);
                const box = detection.detection.box;
                modalOverlayCtx.strokeStyle = '#00b894'; modalOverlayCtx.lineWidth = 2;
                modalOverlayCtx.strokeRect(box.x, box.y, box.width, box.height);
                const ear = (function(){ const l=detection.landmarks.getLeftEye(); const r=detection.landmarks.getRightEye(); const A=(Math.hypot(l[1].x-l[5].x,l[1].y-l[5].y)+Math.hypot(l[2].x-l[4].x,l[2].y-l[4].y)); const B=(Math.hypot(r[1].x-r[5].x,r[1].y-r[5].y)+Math.hypot(r[2].x-r[4].x,r[2].y-r[4].y)); const C=(Math.hypot(l[0].x-l[3].x,l[0].y-l[3].y)+Math.hypot(r[0].x-r[3].x,r[0].y-r[3].y))/2; return (A+B)/(2.0*C); })();
                modalOverlayCtx.fillStyle = '#fff'; modalOverlayCtx.font = '14px Arial';
                modalOverlayCtx.fillText(`EAR:${ear.toFixed(2)} TURN:${head.toFixed(2)}`, Math.max(8, box.x), Math.max(18, box.y-6));
                const pts = detection.landmarks.positions;
                modalOverlayCtx.save(); modalOverlayCtx.strokeStyle='rgba(255,255,255,0.9)'; modalOverlayCtx.lineWidth=1;
                function poly(idxs, close=false){ modalOverlayCtx.beginPath(); for(let i=0;i<idxs.length;i++){ const p=pts[idxs[i]]; if(i===0) modalOverlayCtx.moveTo(p.x,p.y); else modalOverlayCtx.lineTo(p.x,p.y);} if(close) modalOverlayCtx.closePath(); modalOverlayCtx.stroke(); }
                poly([...Array(17).keys()]); poly([17,18,19,20,21]); poly([22,23,24,25,26]); poly([27,28,29,30,31,32,33,34,35]); poly([36,37,38,39,40,41], true); poly([42,43,44,45,46,47], true); poly([48,49,50,51,52,53,54,55,56,57,58,59], true); poly([60,61,62,63,64,65,66,67], true);
                modalOverlayCtx.restore();
                if (head < -0.12) { modalOverlayCtx.fillStyle = '#ff6b6b'; modalOverlayCtx.fillText('TURN LEFT', 8, modalOverlay.height - 12); }
                else if (head > 0.12) { modalOverlayCtx.fillStyle = '#ff6b6b'; modalOverlayCtx.fillText('TURN RIGHT', 8, modalOverlay.height - 12); }
                else { modalOverlayCtx.fillStyle = '#7bed9f'; modalOverlayCtx.fillText('CENTER', 8, modalOverlay.height - 12); }
            }

            if (Math.abs(head) < 0.12) { consecutive++; } else { consecutive = 0; }
            if (consecutive >= NEED_CONSECUTIVE) break;
            await new Promise(r=>setTimeout(r,80));
        }

        if (consecutive < NEED_CONSECUTIVE) {
            modalStatus.textContent = 'Face was not stable/centered. Please try again.';
            modalCaptureBtn.disabled = false;
            return;
        }

        modalStatus.textContent = 'Stable face detected — capturing descriptors...';
        const descriptors = [];
        for (let i = 0; i < 3; i++) {
            modalStatus.textContent = `Capturing sample ${i+1} of 3...`;
            const detection = await faceapi.detectSingleFace(modalVideo, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!detection) throw new Error('No face detected for sample ' + (i+1));
            descriptors.push(Array.from(detection.descriptor));
            await new Promise(res => setTimeout(res, 300));
        }
        const averaged = avgDescriptors(descriptors);
        // Submit both raw samples and the averaged descriptor for server-side robustness
        document.getElementById(currentInputId).value = JSON.stringify({ samples: descriptors, average: averaged });
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