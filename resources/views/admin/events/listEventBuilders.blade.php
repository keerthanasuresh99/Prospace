@extends('admin.app')
@php
    $page_title = 'Dashboard';
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 h3">Event Builders</h5>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="table-responsive fixTableHead">
                        <table id="myTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>FIRST NAME</th>
                                    <th>LAST NAME</th>
                                    <th>PLACE</th>
                                    <th>PHONE</th>
                                    <th>STATUS</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list as $key => $item)
                                    <tr class="data-row">
                                        <td>{{ $key + 1 }}</td>
                                        <td class="first_name">{{ $item->first_name }}</td>
                                        <td class="last_name">{{ $item->last_name }}</td>
                                        <td class="place">{{ $item->place }}</td>
                                        <td class="phone">{{ $item->phone }}</td>
                                        <td class="status">{{ $item->event_builder == 1 ? 'Approved' : 'Waiting for approval' }}</td>
                                        <td>
                                            <form id="approvalForm{{ $item->id }}"
                                                action="{{ route('event-builders.approve', $item->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success"{{ $item->event_builder == 1 ? 'disabled' : '' }}>Approve</button>
                                            </form>
                                            <form id="rejectionForm{{ $item->id }}"
                                                action="{{ route('event-builders.reject', $item->id) }}" method="POST"
                                                class="d-inline" style="display: none;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('foot_scripts')
    <script>
        $(document).ready(function() {
            $('.color').colorpicker({
                format: 'hex' // Set format to 'hex' for hexadecimal values
            });

            var table = $('#myTable').DataTable({
                "displayLength": 100,
            });

            $(document).on('click', "#edit-item", function() {
                $(this).addClass('edit-item-trigger-clicked');
                $('#edit-modal').modal('show'); // Trigger modal show
            });


            // on modal show
            $('#edit-modal').on('show.bs.modal', function() {
                var el = $(".edit-item-trigger-clicked");
                var row = el.closest(".data-row");
                var id = el.data('item-id');
                var options = {
                    'backdrop': 'static'
                };
                $.ajax({
                    url: "{{ route('edit-template') }}",
                    type: "GET",
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(result) {
                        console.log(result);
                        $("#modal-input-id").val(result.id);
                        $('#modal-input-achiever').val(result.achiever_id);
                        $("#image_position").val(result.image_position);
                        $("#modal-input-name-color").val(result.colour_for_name);
                        $("#modal-input-date-color").val(result.colour_for_date);

                        var select = document.getElementById("modal-input-achiever");
                        if (select) {
                            var events2 = new Event("change");
                            select.sub_achiever_id = result.sub_achiever_id;
                            select.dispatchEvent(events2);
                        }

                        var newImage = document.getElementById('new_image')
                        newImage.style.display = "none";
                        document.getElementById('edit_file_name_class').innerHTML =
                            '     <label for="name" class="control-label">Background Image</label>';
                        var file = result.image;
                        if (file) {
                            var fileicon = "{{ asset('storage/templates/') }}" + '/' + file;
                            $('#edit_file_name_class').append('<ul id ="file" class="no-bullets">\n\
                                            					<li>\n\
                                            					<ul class="ul-franchise-view no-bullets" id="file-1">\n\
                                            					<li style="margin-right:6px"><input type="text" value ="' + file +
                                '" data=index class="form-control remove_input" readonly="true"></li>\n\
                                            					<li style="margin-right:6px;margin-top:5px"> <a class="fa fa-eye btn btn-xs btn-warning remove_a view-a" title="View Attachments"  target="_blank" href="' +
                                fileicon +
                                '"></a></li>\n\
                                                                <li style="margin-right:6px;margin-top:5px"> <a class="fa fa-trash btn btn-xs btn-danger btn-delete delete-file-btn" onclick="deleteFile()" title="Delete" value=""></a></li>\n\
                                                                </ul>\n\
                                            					</li>\n\
                                            					</ul>');

                        }
                    }
                });
            });

            // on modal hide
            $('#edit-modal').on('hide.bs.modal', function() {
                $('.edit-item-trigger-clicked').removeClass('edit-item-trigger-clicked')
                $("#edit-form").trigger("reset");
            });


            $("#frmTemplate").validate({
                onkeyup: function(element) {
                    $(element).valid(); // Trigger validation on keyup
                },
                onfocusout: function(element) {
                    $(element).valid(); // Trigger validation on focus out
                },
                rules: {
                    achiever: {
                        required: true
                    },
                    image: {
                        required: true
                    },
                    image_position: {
                        required: true
                    }
                },
                errorElement: "span",
                errorClass: "help-inline-error"
            });


            $("#frmEditTemplate").validate({
                onkeyup: function(element) {
                    $(element).valid(); // Trigger validation on keyup
                },
                onfocusout: function(element) {
                    $(element).valid(); // Trigger validation on focus out
                },
                rules: {
                    achiever: {
                        required: true
                    },
                    image: {
                        required: true
                    },
                    image_position: {
                        required: true
                    }
                },
                errorElement: "span",
                errorClass: "help-inline-error",
            });

        })


        $('.delete-btn').click(function() {
            var itemId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete route
                    $.ajax({
                        url: "{{ route('delete-template') }}",
                        type: 'GET',
                        data: {
                            id: itemId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Error!',
                                text: '"Deleted!", "Your file has been deleted"',
                                icon: 'error',
                            });
                            window.setTimeout(function() {
                                window.location.reload()
                            }, 1000);
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            console.log(xhr.responseText);
                        }
                    });
                }
            });
        });

        $('.achiever-dropdown').on('change', function() {
            var achiever = this.value;
            var sub_achiever_id = this.sub_achiever_id;
            $.ajax({
                url: "{{ route('fetch-subachievers') }}",
                type: "POST",
                data: {
                    id: achiever,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(res) {
                    $('.subachiever-dropdown').html('<option value="">Select Sub Achiever</option>');
                    $.each(res.subachievers, function(key, value) {
                        $(".subachiever-dropdown").append('<option value="' + value
                            .id + '">' + value.value + '</option>');
                    });
                    if (sub_achiever_id != undefined) {
                        $('#modal-input-subachiever').val(sub_achiever_id);
                    }
                    sub_achiever_id = "";
                }
            });

        });

        function deleteFile() {
            $('#file-1').remove();
            var editImage = document.getElementById('edit_file_name_class')
            editImage.style.display = "none";
            var newImage = document.getElementById('new_image')
            newImage.style.display = "block";
        }
    </script>
@endsection
