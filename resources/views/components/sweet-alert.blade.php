@php
    $successData = session('success');
    if (is_string($successData)) {
        $successData = ['id' => uniqid(), 'message' => $successData];
    }

    $errorData = session('error');
    if (is_string($errorData)) {
        $errorData = ['id' => uniqid(), 'message' => $errorData];
    }
@endphp

@if ($successData)
    <script>
        const successId = sessionStorage.getItem('success_id');
        const currentSuccessId = @json($successData['id']);

        if (!successId || successId !== currentSuccessId) {
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: @json($successData['message']),
                confirmButtonText: 'OK'
            }).then(() => {
                sessionStorage.setItem('success_id', currentSuccessId);
                fetch("{{ route('session.clear', 'success') }}");
            });
        }
    </script>
@endif

@if ($errorData)
    <script>
        const errorId = sessionStorage.getItem('error_id');
        const currentErrorId = @json($errorData['id']);

        if (!errorId || errorId !== currentErrorId) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json($errorData['message']),
                confirmButtonText: 'OK'
            }).then(() => {
                sessionStorage.setItem('error_id', currentErrorId);
                fetch("{{ route('session.clear', 'error') }}");
            });
        }
    </script>
@endif
