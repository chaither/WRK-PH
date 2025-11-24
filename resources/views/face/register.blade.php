
@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
<div class="container mx-auto px-6 py-6">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold mb-4">Face Registration</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        <p class="text-sm text-gray-600 mb-4">Use your webcam to register your face. Make sure your face is well lit and centered. We will capture a descriptor (embedding) and store it securely.</p>

        <div class="mb-4">
            <video id="video" width="400" height="300" autoplay muted playsinline class="border rounded"></video>
        </div>

        <div class="mb-3">
            <div id="registerStatus" class="text-sm text-gray-600">Ready to capture. Please position your face in front of the camera.</div>
        </div>

        <div class="flex gap-3">
            @if(empty($user->face_embedding))
                <button id="captureBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Capture & Register (3 samples)</button>
            @else
                <button disabled class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Face Already Registered</button>
            @endif
            <a href="{{ route('dtr.index') }}" class="bg-gray-200 px-4 py-2 rounded">Cancel</a>
        </div>

        <form id="faceForm" method="POST" action="{{ route('face.register.store') }}" style="display:none;" novalidate>
            @csrf
            <input type="hidden" name="face_descriptor" id="face_descriptor_input">
        </form>

        <p class="text-xs text-gray-500 mt-4">Note: This demo uses client-side face-api.js to compute a face descriptor. You can switch to a server-side provider (Azure/ AWS Rekognition) if preferred.</p>
    </div>
</div>

@push('scripts')
<!-- face-api.js from unpkg (uses TensorFlow.js) -->
<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const captureBtn = document.getElementById('captureBtn');
const faceInput = document.getElementById('face_descriptor_input');
const faceForm = document.getElementById('faceForm');
const statusEl = document.getElementById('registerStatus');

async function startVideo() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        video.srcObject = stream;
        await video.play();
    } catch (err) {
        statusEl.textContent = 'Cannot access camera: ' + err.message;
    }
}

async function loadModels() {
    // Try local models first (recommended). Put model files under public/models
    const local = '/models';
    const cdn = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

    async function tryLoad(url) {
        await faceapi.nets.tinyFaceDetector.loadFromUri(url);
        await faceapi.nets.faceLandmark68Net.loadFromUri(url);
        await faceapi.nets.faceRecognitionNet.loadFromUri(url);
    }

    try {
        await tryLoad(local);
        return;
    } catch (e) {
        console.warn('Local models not found at', local, e);
    }

    try {
        await tryLoad(cdn);
        return;
    } catch (e) {
        console.error('CDN models failed to load from', cdn, e);
        throw new Error('Face models could not be loaded. Place models under /public/models or ensure CDN is reachable.');
    }
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

captureBtn && captureBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    captureBtn.disabled = true;
    statusEl.textContent = 'Capturing 3 samples — please slowly move your head slightly between captures.';

    try {
        const samples = [];
        for (let i = 0; i < 3; i++) {
            statusEl.textContent = `Capturing sample ${i+1} of 3...`;
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!detection) {
                throw new Error('No face detected on sample ' + (i+1));
            }
            samples.push(Array.from(detection.descriptor));
            await new Promise(res => setTimeout(res, 700));
        }

        const averaged = avgDescriptors(samples);
        faceInput.value = JSON.stringify(averaged);
        statusEl.textContent = 'Submitting registration...';
        faceForm.submit();
    } catch (err) {
        statusEl.textContent = 'Capture failed: ' + err.message;
        console.error(err);
        captureBtn.disabled = false;
    }
});

// Initialize
Promise.all([loadModels()]).then(startVideo).catch(err => {
    console.error(err);
    statusEl.textContent = 'Failed to load face models.';
});
</script>
@endpush

@endsection
