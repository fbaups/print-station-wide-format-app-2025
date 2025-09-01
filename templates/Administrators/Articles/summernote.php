<script>
    $(document).ready(function () {
        let opts = {
            height: 500
        }

        $('#body').summernote(opts);

        $('.note-btn').removeClass('btn-outline-secondary').addClass('btn-outline-dark');
    });
</script>
