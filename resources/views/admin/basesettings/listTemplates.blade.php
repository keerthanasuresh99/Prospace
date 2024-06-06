@extends('admin.app')
@php
    $page_title = 'Dashboard';
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 h3">Templates</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-rounded mb-3" data-toggle="modal" data-target="#responsive-modal"><i
                                class="mdi mdi-plus"></i>Add Templates</button>
                    </div>
                    <div class="table-responsive fixTableHead">
                        <table id="myTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ACHIEVER</th>
                                    <th>SUB ACHIEVER</th>
                                    <th>BACKGROUND IMAGE</th>
                                    <th>IMAGE POSITION</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list as $key => $item)
                                    <tr class="data-row">
                                        <td>{{ $key + 1 }}</td>
                                        <td class="achiever" data-achiever-id="{{ $item->achiever_id }}">
                                            {{ $item->achievers->value }}
                                        </td>
                                        <td class="subachiever" data-subacheiver-id="{{ $item->sub_achiever_id }}">
                                            @if ($item->subAchievers)
                                                {{ $item->subAchievers->value }}
                                            @else
                                            @endif
                                        </td>
                                        <td class="image">{{ $item->image }}</td>
                                        <td class="image_position">{{ $item->image_position }}</td>
                                        <td>
                                            <span class="btn btn-primary" id="edit-item" data-item-id="{{ $item->id }}"
                                                data-toggle="modal" data-target="#edit-modal"><i
                                                    class="fa-solid fa-pencil"></i></span>
                                            <button class="btn btn-danger delete-btn" data-id="{{ $item->id }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
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
    <div id="responsive-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Template</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('add-template') }}" method="post" id="frmTemplate" enctype="multipart/form-data"
                    class="form-horizontal  form-material">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Acheiver</label>
                                <select class="form-control achiever-dropdown" required name="achiever">
                                    <option value="">Select Achiever</option>
                                    @foreach ($achievers_lists as $list)
                                        <option value={{ $list->id }}>{{ $list->value }}</option>
                                    @endforeach
                                </select>
                                <span class="bar"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="control-label">Sub Acheiver</label>
                                <select class="form-control subachiever-dropdown"  name="sub_achiever">
                                    <option value="">Select Sub Achiever</option>
                                </select>
                                <span class="bar"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Background Image</label>
                                <input type="file" class="form-control" required name="image"
                                    accept="image/jpeg, image/jpg, image/png">
                                <span class="bar"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="control-label">Position of Image</label>
                                <select class="form-control" required name="image_position">
                                    <option value="">Select Position</option>
                                    <option value="center">Center</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                </select>
                                <span class="bar"></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Select Colour for Name</label>
                                <input class="form-control color" type="text" name="color_for_name" />
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="control-label">Select Colour for Date</label>
                                <input class="form-control color" type="text" name="color_for_date" />
                            </div>
                        </div>
                        <div class="rightActions">
                            <button type="submit" class="btn btn-primary btn-rounded waves-effect" name="addAchiever">
                                <i class="mdi mdi-account-check"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit-modal-label"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Template</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('update-template') }}" method="post" id="frmEditTemplate"
                    enctype="multipart/form-data" class="form-horizontal  form-material">
                    @csrf
                    <input type="hidden" name="id" id="modal-input-id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Acheiver</label>
                                <select class="form-control achiever-dropdown" name="achiever" required
                                    id="modal-input-achiever">
                                    <option value="">Select Achiever</option>
                                    @foreach ($achievers_lists as $list)
                                        <option value={{ $list->id }}>{{ $list->value }}</option>
                                    @endforeach
                                </select>
                                <span class="bar"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="control-label">Sub Acheiver</label>
                                <select class="form-control subachiever-dropdown"  name="sub_achiever"
                                    id="modal-input-subachiever">
                                    <option value="">Select Sub Achiever</option>
                                </select>
                                <span class="bar"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="form-group row d-flex align-items-center">
                                    <div class="col-md-6" id="edit_file_name_class">
                                    </div>
                                    <div class="col-md-6" id="new_image" style="display: none">
                                        <label for="name" class="control-label">Background Image</label>
                                        <input type="file" class="form-control" required name="image"
                                            accept="image/jpeg, image/jpg, image/png" id="modal-input-image">
                                        <span class="bar"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name" class="control-label">Position of Image</label>
                                        <select class="form-control" required name="image_position" id="image_position">
                                            <option value="">Select Position</option>
                                            <option value="center">Center</option>
                                            <option value="left">Left</option>
                                            <option value="right">Right</option>
                                        </select>
                                        <span class="bar"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Select Colour for name</label>
                                <input class="form-control color" type="text" id="modal-input-name-color" name="color_for_name" />
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="control-label">Select Colour for date</label>
                                <input class="form-control color" type="text" id="modal-input-date-color" name="color_for_date" />
                            </div>
                        </div>
                        <div class="rightActions">
                            <button type="submit" class="btn btn-primary btn-rounded waves-effect" name="addAchiever">
                                <i class="mdi mdi-account-check"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
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
                "displayLength": 50,
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
