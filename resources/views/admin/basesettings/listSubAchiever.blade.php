@extends('admin.app')
@php
    $page_title = 'Dashboard';
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 h3">Sub Achiever List</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-rounded mb-3" data-toggle="modal" data-target="#responsive-modal"><i
                                class="mdi mdi-plus"></i>Add Sub Achievers List</button>
                    </div>

                    <div class="table-responsive m-t-40 fixTableHead">
                        <table id="myTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ACHIEVER</th>
                                    <th>SUB ACHIEVER</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list as $key => $item)
                                    <tr class="data-row">
                                        <td>{{ $key + 1 }}</td>
                                        <td class="achiever" data-achiever-id="{{ $item->achiever_id }}">
                                            {{ $item->achievers->value }}</td>
                                        <td class="name">{{ $item->value }}</td>
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
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add List</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('add-sub-achiever') }}" method="post" id="frmAchiever"
                    class="form-horizontal  form-material">
                    @csrf
                    <div class="modal-body d-flex flex-column ">
                        <div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="name" class="control-label">Acheiver</label>
                                    <select class="form-control brandTextColorPink" required name="achiever">
                                        <option value="">Select Achiever</option>
                                        @foreach ($achievers_lists as $list)
                                            <option value={{ $list->id }}>{{ $list->value }}</option>
                                        @endforeach
                                    </select>
                                    <span class="bar"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="name" class="control-label">Value</label>
                                    <input type="text" class="form-control" required name="value">
                                    <span class="bar"></span>
                                </div>
                            </div>
                            <div class="rightActions">
                                <button type="submit" class="btn btn-primary btn-rounded waves-effect" name="addAchiever">
                                    <i class="mdi mdi-account-check"></i> Save
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit-modal-label"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit List</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('update-subachiever') }}" method="post" id="frmEditAchiever"
                    class="form-horizontal  form-material">
                    @csrf
                    <input type="hidden" name="id" id="modal-input-id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="control-label">Acheiver</label>
                                <select class="form-control" name="achiever" required id="modal-input-achiever">
                                    <option value="">Select Achiever</option>
                                    @foreach ($achievers_lists as $list)
                                        <option value={{ $list->id }}>{{ $list->value }}</option>
                                    @endforeach
                                </select>
                                <span class="bar"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="modal-input-name" class="control-label">Value</label>
                                <input type="text" class="form-control" required="" id="modal-input-name"
                                    name="value">
                                <span class="bar"></span>
                            </div>
                        </div>
                        <div class="rightActions">
                            <button type="submit" class="btn btn-primary btn-rounded waves-effect" name="addAchiever">
                                <i class="mdi mdi-account-check"></i> Update
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

                var achieverId = row.children(".achiever").data('achiever-id');
                var name = row.children(".name").text();
                console.log(achieverId);
                // fill the data in the input fields
                $("#modal-input-id").val(id);
                $("#modal-input-name").val(name);
                $("#modal-input-achiever").val(achieverId);
            });

            // on modal hide
            $('#edit-modal').on('hide.bs.modal', function() {
                $('.edit-item-trigger-clicked').removeClass('edit-item-trigger-clicked')
                $("#edit-form").trigger("reset");
            });


            $("#frmAchiever").validate({
                onkeyup: function(element) {
                    $(element).valid(); // Trigger validation on keyup
                },
                onfocusout: function(element) {
                    $(element).valid(); // Trigger validation on focus out
                },
                rules: {
                    value: {
                        required: true
                    },
                    description: {
                        required: true
                    },
                },
                errorElement: "span",
                errorClass: "help-inline-error",
            });


            $("#frmEditAchiever").validate({
                onkeyup: function(element) {
                    $(element).valid(); // Trigger validation on keyup
                },
                onfocusout: function(element) {
                    $(element).valid(); // Trigger validation on focus out
                },
                rules: {
                    value: {
                        required: true
                    },
                    description: {
                        required: true
                    },
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
                        url: "{{ route('delete-sub-achiever') }}",
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
                            }, 2000);
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            console.log(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endsection
