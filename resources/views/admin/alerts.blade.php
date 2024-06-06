<script type="text/javascript">
    function showMessage(content, type) {
        console.log("here");
        $.toast({
            heading: type.charAt(0).toUpperCase() + type.slice(1),
            text: content,
            position: 'top-center',
            loaderBg: '#ffffff99',
            icon: type,
            hideAfter: 3500,
            stack: 6
        });

    }
    $(document).ready(function() {
        @if (Session::has('msg_success'))
            showMessage('{{ Session::get('msg_success') }}', 'success');
        @endif
        @if (Session::has('msg_error'))
            showMessage('{{ Session::get('msg_error') }}', 'error');
        @endif
        @if (Session::has('msg_info'))
            showMessage('{{ Session::get('msg_info') }}', 'info');
        @endif
        @if (Session::has('msg_warning'))
            showMessage('{{ Session::get('msg_warning') }}', 'warning');
        @endif
    });
</script>
