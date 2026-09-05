@extends('frontend.user.buyer.buyer-master')

@section('site-title')
    {{ __('Start Streaming') }}
@endsection

@section('style')
    <style>
        .stream-setup-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            padding: 32px;
            margin-bottom: 24px;
        }
        .stream-setup-card h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 24px;
        }
        .stream-form-group {
            margin-bottom: 20px;
        }
        .stream-form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .stream-form-group input,
        .stream-form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .stream-form-group input:focus,
        .stream-form-group textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }
        .stream-form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Camera Preview */
        .camera-preview-wrap {
            margin-bottom: 20px;
        }
        .camera-preview-box {
            width: 100%;
            max-width: 480px;
            height: 270px;
            background: #111;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .camera-preview-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        .camera-preview-box .placeholder-text {
            color: #666;
            font-size: 14px;
            text-align: center;
        }
        .camera-preview-box .placeholder-text i {
            display: block;
            font-size: 40px;
            color: #444;
            margin-bottom: 8px;
        }
        .btn-test-camera {
            margin-top: 10px;
            padding: 8px 20px;
            border: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-test-camera:hover {
            border-color: #6366f1;
            color: #6366f1;
        }
        .btn-test-camera.active {
            background: #6366f1;
            color: #fff;
            border-color: #6366f1;
        }

        /* Go Live Button */
        .btn-go-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            padding: 14px 36px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-go-live:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .btn-go-live:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Stream History */
        .stream-history-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            padding: 24px;
        }
        .stream-history-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 16px;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .history-table th {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #f0f0f0;
            color: #888;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #f5f5f5;
            color: #333;
        }
        .history-table tr:last-child td { border-bottom: none; }
        .badge-live {
            display: inline-block;
            background: #22c55e;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-ended {
            display: inline-block;
            background: #94a3b8;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }
        .empty-state {
            text-align: center;
            padding: 32px;
            color: #999;
            font-size: 14px;
        }
        .empty-state i {
            font-size: 36px;
            display: block;
            margin-bottom: 8px;
            color: #ccc;
        }
        .error-msg {
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
            display: none;
        }
    </style>
@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>

    @include('frontend.user.seller.partials.sidebar-two')

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')

        <div class="dashboard__body">
            <div class="dashboard__inner">

                <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboards-title"><strong>{{ __('Start Streaming') }}</strong></h4>
                        </div>
                    </div>
                </div>

                {{-- Go Live Setup --}}
                <div class="stream-setup-card">
                    <h3><i class="las la-broadcast-tower" style="color:#6366f1"></i> {{ __('Go Live') }}</h3>

                    <div class="stream-form-group">
                        <label for="streamTitle">{{ __('Stream Title') }} <span style="color:#ef4444">*</span></label>
                        <input type="text" id="streamTitle" placeholder="{{ __('e.g. Building a Laravel App') }}" maxlength="120">
                        <div class="error-msg" id="titleError">{{ __('Please enter a stream title') }}</div>
                    </div>

                    <div class="stream-form-group">
                        <label for="streamDesc">{{ __('Description') }} <small style="color:#999">({{ __('optional') }})</small></label>
                        <textarea id="streamDesc" rows="3" placeholder="{{ __('What will you be streaming about?') }}"></textarea>
                    </div>

                    <div class="camera-preview-wrap">
                        <label style="font-weight:600;color:#333;font-size:14px;margin-bottom:6px;display:block">{{ __('Camera Preview') }}</label>
                        <div class="camera-preview-box" id="cameraPreviewBox">
                            <video id="cameraPreview" autoplay muted playsinline></video>
                            <div class="placeholder-text" id="cameraPlaceholder">
                                <i class="las la-video"></i>
                                {{ __('Click "Test Camera" to preview') }}
                            </div>
                        </div>
                        <button type="button" class="btn-test-camera" id="testCameraBtn">
                            <i class="las la-camera"></i> {{ __('Test Camera') }}
                        </button>
                    </div>

                    <button type="button" class="btn-go-live" id="goLiveBtn">
                        <i class="las la-broadcast-tower"></i> {{ __('Go Live') }}
                    </button>
                </div>

                {{-- Previous Streams --}}
                <div class="stream-history-card">
                    <h3><i class="las la-history" style="color:#6366f1"></i> {{ __('Previous Streams') }}</h3>

                    @if(isset($pastStreams) && $pastStreams->count() > 0)
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastStreams as $s)
                                <tr>
                                    <td>{{ $s->title ?: __('Untitled Stream') }}</td>
                                    <td>{{ $s->started_at ? \Carbon\Carbon::parse($s->started_at)->format('M d, Y H:i') : '-' }}</td>
                                    <td>
                                        @if($s->started_at && $s->ended_at)
                                            {{ \Carbon\Carbon::parse($s->started_at)->diffForHumans(\Carbon\Carbon::parse($s->ended_at), true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($s->is_live)
                                            <span class="badge-live">LIVE</span>
                                        @else
                                            <span class="badge-ended">Ended</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="las la-video-slash"></i>
                            {{ __('No previous streams yet. Start your first stream!') }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Camera preview
        let cameraStream = null;
        const previewVideo = document.getElementById('cameraPreview');
        const placeholder = document.getElementById('cameraPlaceholder');
        const testBtn = document.getElementById('testCameraBtn');

        testBtn.addEventListener('click', async function() {
            if (cameraStream) {
                // Stop camera
                cameraStream.getTracks().forEach(t => t.stop());
                cameraStream = null;
                previewVideo.style.display = 'none';
                placeholder.style.display = 'block';
                testBtn.classList.remove('active');
                testBtn.innerHTML = '<i class="las la-camera"></i> {{ __("Test Camera") }}';
            } else {
                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    previewVideo.srcObject = cameraStream;
                    previewVideo.style.display = 'block';
                    placeholder.style.display = 'none';
                    testBtn.classList.add('active');
                    testBtn.innerHTML = '<i class="las la-stop"></i> {{ __("Stop Camera") }}';
                } catch (e) {
                    alert('{{ __("Could not access camera. Please check permissions.") }}');
                }
            }
        });

        // Go Live
        document.getElementById('goLiveBtn').addEventListener('click', function() {
            const title = document.getElementById('streamTitle').value.trim();
            const desc = document.getElementById('streamDesc').value.trim();
            const titleError = document.getElementById('titleError');

            if (!title) {
                titleError.style.display = 'block';
                document.getElementById('streamTitle').focus();
                return;
            }
            titleError.style.display = 'none';

            // Stop camera preview before navigating
            if (cameraStream) {
                cameraStream.getTracks().forEach(t => t.stop());
                cameraStream = null;
            }

            const url = '/live?role=host&title=' + encodeURIComponent(title) + '&description=' + encodeURIComponent(desc);
            window.location.href = url;
        });

        // Hide error on typing
        document.getElementById('streamTitle').addEventListener('input', function() {
            document.getElementById('titleError').style.display = 'none';
        });

        // Clean up on page leave
        window.addEventListener('beforeunload', function() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(t => t.stop());
            }
        });
    </script>
@endsection
