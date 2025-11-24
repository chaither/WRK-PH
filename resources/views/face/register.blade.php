
@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto bg-gray-50 rounded-lg shadow-lg overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-stretch">
            <!-- Camera area -->
            <div class="md:w-1/2 bg-gradient-to-br from-gray-200 via-gray-900 to-gray-100 p-6 flex flex-col items-center md:items-center md:justify-center h-100">
                <div class="w-48 h-48 md:w-64 md:h-64 relative camera-viewport">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-48 h-48 md:w-64 md:h-64 rounded-full border-4 border-dashed border-green-400 p-2 flex items-center justify-center bg-black/30 shadow-lg">
                            <div style="width:100%;height:100%;border-radius:9999px;overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;">
                                <video id="video" autoplay muted playsinline class="w-full h-full object-cover bg-black"></video>
                                <canvas id="overlay" style="position:absolute; left:0; top:0; pointer-events:none;"></canvas>
                                <!-- Arrow indicator placed inside the circle -->
                                <div id="arrowOverlay" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 transition-opacity duration-200">
                                    <div id="arrowInnerOverlay" class="w-24 h-6 flex items-center justify-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Centered status below the circle -->
                <div class="w-full mt-3 flex flex-col items-center">
                    <div id="registerStatus" class="font-medium text-gray-200">Ready — position your face inside the ring</div>
                    <div id="registerProgress" class="text-xs text-gray-300 mt-1">0% complete</div>
                </div>
            </div>

            <!-- Instructions + controls (beside the circle) -->
            <div class="md:w-1/2 p-6 flex flex-col justify-start h-full">
                <h2 class="text-2xl font-semibold text-gray-800">Face Registration</h2>
                <p class="text-sm text-gray-600 mt-1">Take a photo of your face to enable Face Verification. Ensure adequate lighting and remove glasses or masks for the most accurate capture.</p>

                <div class="mt-4 bg-gray-50 p-3 rounded">
                    <h3 class="text-sm font-medium text-gray-700">Steps</h3>
                    <ol class="mt-2 list-decimal list-inside text-sm text-gray-600 space-y-1">
                        <li>Center your face inside the ring.</li>
                        <li>Follow the on-screen prompts (center, left, right).</li>
                        <li>Hold still while samples are captured.</li>
                    </ol>
                </div>

                <div class="mt-6 flex flex-row gap-3 items-center">
                    @if(empty($user->face_embedding))
                        <button id="captureBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded shadow">Capture & Register</button>
                    @else
                        <button disabled class="flex-1 bg-gray-300 text-gray-700 font-medium py-3 rounded">Face Already Registered</button>
                    @endif
                    <a href="{{ route('dtr.index') }}" class="inline-block px-4 py-3 border rounded text-sm text-gray-700 hover:bg-gray-100">Cancel</a>
                </div>

                <p class="text-xs text-gray-500 mt-4">We store a numeric face descriptor — not your photo — and it is used only for verification within this system.</p>

                <form id="faceForm" method="POST" action="{{ route('face.register.store') }}" style="display:none;" novalidate>
                    @csrf
                    <input type="hidden" name="face_descriptor" id="face_descriptor_input">
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
// Elements
const video = document.getElementById('video');
const captureBtn = document.getElementById('captureBtn');
const faceInput = document.getElementById('face_descriptor_input');
const faceForm = document.getElementById('faceForm');
const statusEl = document.getElementById('registerStatus');
const progressEl = document.getElementById('registerProgress');
const arrowOverlay = document.getElementById('arrowOverlay');
const arrowInnerOverlay = document.getElementById('arrowInnerOverlay');

// Load models (CDN first then local)
async function loadModels() {
    const local = '/models';
    const cdn = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
    async function tryLoad(url) {
        await faceapi.nets.tinyFaceDetector.loadFromUri(url);
        await faceapi.nets.faceLandmark68Net.loadFromUri(url);
        await faceapi.nets.faceRecognitionNet.loadFromUri(url);
    }
    try { await tryLoad(cdn); console.log('Loaded models from CDN', cdn); return; } catch(e){ console.warn('CDN model load failed, trying local', e); }
    try { await tryLoad(local); console.log('Loaded models from', local); return; } catch(e){ console.error('Models failed to load from both CDN and local', e); throw e; }
}

// Start camera and ensure overlay canvas matches video size
async function startVideo() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
        video.srcObject = stream;
        await video.play();

        const canvas = document.getElementById('overlay');
        function resizeOverlay() {
            if (!canvas || !video) return;
            const w = video.clientWidth;
            const h = video.clientHeight;
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w;
                canvas.height = h;
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';
            }
        }
        resizeOverlay();
        window.addEventListener('resize', resizeOverlay);
        video.addEventListener('loadedmetadata', resizeOverlay);
        setTimeout(resizeOverlay, 300);
    } catch (err) {
        statusEl.textContent = 'Cannot access camera: ' + err.message;
    }
}

function avgDescriptors(descriptors) {
    if (!descriptors.length) return [];
    const len = descriptors[0].length;
    const out = new Array(len).fill(0);
    descriptors.forEach(d => { for (let i = 0; i < len; i++) out[i] += d[i]; });
    for (let i = 0; i < len; i++) out[i] = out[i] / descriptors.length;
    return out;
}

function headTurnNormalized(detection) {
    const box = detection.detection.box;
    const nose = detection.landmarks.getNose()[3];
    const leftEye = detection.landmarks.getLeftEye();
    const rightEye = detection.landmarks.getRightEye();
    const eyeCx = (leftEye.reduce((s,p)=>s+p.x,0)/leftEye.length + rightEye.reduce((s,p)=>s+p.x,0)/rightEye.length)/2;
    const delta = (nose.x - eyeCx) / box.width;
    return delta;
}

function drawGuidelines(ctx, detection) {
    if (!detection) return;
    const pts = detection.landmarks.positions;
    ctx.save(); ctx.strokeStyle = 'rgba(255,255,255,0.9)'; ctx.lineWidth = 1;
    function poly(idxs, close=false){ ctx.beginPath(); for(let i=0;i<idxs.length;i++){ const p=pts[idxs[i]]; if(i===0) ctx.moveTo(p.x,p.y); else ctx.lineTo(p.x,p.y);} if(close) ctx.closePath(); ctx.stroke(); }
    poly([...Array(17).keys()]); poly([17,18,19,20,21]); poly([22,23,24,25,26]); poly([27,28,29,30,31,32,33,34,35]); poly([36,37,38,39,40,41], true); poly([42,43,44,45,46,47], true); poly([48,49,50,51,52,53,54,55,56,57,58,59], true); poly([60,61,62,63,64,65,66,67], true);
    ctx.restore();
}

// Arrow animation helpers
function showArrow(direction) {
    if (!arrowOverlay || !arrowInnerOverlay) return;
    arrowInnerOverlay.innerHTML = '';
    arrowOverlay.style.opacity = '1';
    if (direction === 'left') {
        arrowInnerOverlay.innerHTML = '<svg class="arrow-svg" viewBox="0 0 24 24" width="48" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 19L8 12l7-7" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        arrowInnerOverlay.classList.remove('move-right'); arrowInnerOverlay.classList.add('move-left');
    } else if (direction === 'right') {
        arrowInnerOverlay.innerHTML = '<svg class="arrow-svg" viewBox="0 0 24 24" width="48" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 5l7 7-7 7" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        arrowInnerOverlay.classList.remove('move-left'); arrowInnerOverlay.classList.add('move-right');
    } else {
        // center - pulse
        arrowInnerOverlay.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="#10B981" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="4"/></svg>';
        arrowInnerOverlay.classList.remove('move-left'); arrowInnerOverlay.classList.remove('move-right');
    }
}

function hideArrow() {
    if (!arrowOverlay || !arrowInnerOverlay) return;
    arrowInnerOverlay.classList.remove('move-left'); arrowInnerOverlay.classList.remove('move-right');
    arrowOverlay.style.opacity = '0';
}

async function performGuidedRegistration() {
    captureBtn.disabled = true;
    const steps = [
        {key:'center', prompt:'Look straight at the camera', validator: (d)=> Math.abs(headTurnNormalized(d)) < 0.06},
        {key:'left', prompt:'Turn your head LEFT', validator: (d)=> headTurnNormalized(d) < -0.12},
        {key:'right', prompt:'Turn your head RIGHT', validator: (d)=> headTurnNormalized(d) > 0.12},
    ];

    const capturedDescriptors = [];
    for (let si=0; si<steps.length; si++) {
        const step = steps[si];
        statusEl.textContent = step.prompt;
        for (let c=3;c>0;c--) { statusEl.textContent = step.prompt + ' — starting in ' + c + '...'; await new Promise(r=>setTimeout(r,600)); }

        let passed = false; const start = Date.now(); const timeout = 7000; let consecutive = 0; const needConsecutive = 3;
        while (Date.now() - start < timeout) {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (detection) {
                const head = headTurnNormalized(detection);
                const canvas = document.getElementById('overlay');
                if (canvas) {
                    const ctx = canvas.getContext('2d'); ctx.clearRect(0,0,canvas.width,canvas.height);
                    const box = detection.detection.box; ctx.strokeStyle='#00b894'; ctx.lineWidth=2; ctx.strokeRect(box.x,box.y,box.width,box.height);
                    drawGuidelines(ctx, detection);
                }
                // show arrow animation according to step
                if (step.key === 'left') showArrow('left');
                else if (step.key === 'right') showArrow('right');
                else showArrow('center');

                if (step.validator(detection)) consecutive++; else consecutive = 0;
                if (consecutive >= needConsecutive) { passed = true; if (detection && detection.descriptor) capturedDescriptors.push(Array.from(detection.descriptor)); break; }
            } else { const canvas = document.getElementById('overlay'); if (canvas) canvas.getContext('2d').clearRect(0,0,canvas.width,canvas.height); }
            await new Promise(r=>setTimeout(r,120));
        }
        if (!passed) { statusEl.textContent = 'Step failed: ' + step.prompt + '. Please try again.'; captureBtn.disabled = false; progressEl.textContent = '0%'; return; }
        progressEl.textContent = Math.round(((si+1)/steps.length)*100) + '% complete';
        // small success flash
        showArrow('center');
        await new Promise(r=>setTimeout(r,600));
        hideArrow();
    }

    if (capturedDescriptors.length === 0) {
        statusEl.textContent = 'Capturing descriptors...'; const samples = [];
        for (let i=0;i<3;i++) {
            const d = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!d) { statusEl.textContent = 'Failed to capture face. Please retry.'; captureBtn.disabled=false; progressEl.textContent='0%'; return; }
            samples.push(Array.from(d.descriptor)); progressEl.textContent = Math.round(((i+1)/3)*100) + '% capturing'; await new Promise(r=>setTimeout(r,400));
        }
        faceInput.value = JSON.stringify(avgDescriptors(samples));
    } else {
        faceInput.value = JSON.stringify(avgDescriptors(capturedDescriptors));
    }

    statusEl.textContent = 'Submitting registration...'; progressEl.textContent = 'Finalizing...'; faceForm.submit();
}

captureBtn && captureBtn.addEventListener('click', function(e){ e.preventDefault(); performGuidedRegistration(); });

Promise.all([loadModels()]).then(startVideo).catch(err=>{ console.error(err); statusEl.textContent='Failed to load face models.'; progressEl.textContent='0%'; });
</script>
@endpush

<style>
/* Arrow movement animations */
@keyframes moveLeft {
    0% { transform: translateX(0); }
    50% { transform: translateX(-36px); }
 100% { transform: translateX(0); }
}
@keyframes moveRight {
    0% { transform: translateX(0); }
    50% { transform: translateX(36px); }
 100% { transform: translateX(0); }
}
.move-left { animation: moveLeft 1s ease-in-out infinite; }
.move-right { animation: moveRight 1s ease-in-out infinite; }
#arrowOverlay { transition: opacity 220ms ease; opacity:0; }

/* Camera viewport tweaks: ensure the video and overlay are circular and centered */
.camera-viewport { display:flex; align-items:center; justify-content:center; }
#video, #overlay { border-radius: 9999px; display:block; }

/* Make sure the card allows visible shadows and spacing on small screens */
.max-w-4xl { overflow:hidden; }
</style>

@endsection
