@php
    $successData = session('success');
    if (is_string($successData)) {
        $successData = ['id' => uniqid(), 'message' => $successData];
    }

    $errorData = session('error');
    if (is_string($errorData)) {
        $errorData = ['id' => uniqid(), 'message' => $errorData];
    }

    $successMessage = is_array($successData) ? ($successData['message'] ?? null) : $successData;
    $successId = is_array($successData) ? ($successData['id'] ?? $successMessage) : $successMessage;

    $errorMessage = is_array($errorData) ? ($errorData['message'] ?? null) : $errorData;
    $errorId = is_array($errorData) ? ($errorData['id'] ?? $errorMessage) : $errorMessage;
@endphp

@if ($successMessage)
    <script>
        const successId = sessionStorage.getItem('success_id');
        const currentSuccessId = @json($successId);

        if (!successId || successId !== currentSuccessId) {
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: @json($successMessage),
                confirmButtonText: 'OK'
            }).then(() => {
                sessionStorage.setItem('success_id', currentSuccessId);
                fetch("{{ route('session.clear', 'success') }}");
            });
        }
    </script>
@endif

@if ($errorMessage)
    <script>
        const errorId = sessionStorage.getItem('error_id');
        const currentErrorId = @json($errorId);

        if (!errorId || errorId !== currentErrorId) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json($errorMessage),
                confirmButtonText: 'OK'
            }).then(() => {
                sessionStorage.setItem('error_id', currentErrorId);
                fetch("{{ route('session.clear', 'error') }}");
            });
        }
    </script>
@endif
