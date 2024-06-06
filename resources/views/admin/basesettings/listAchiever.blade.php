@extends('admin.app')
@php
    $page_title = 'Dashboard';
@endphp
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 h3">Achiever List</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-rounded mb-3" data-toggle="modal" data-target="#responsive-modal"><i
                                class="mdi mdi-plus"></i>Add Achievers List</button>
                    </div>

                    <div class="table-responsive m-t-40 fixTableHead">
                        <table id="myTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NAME</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list as $key => $item)
                                    <tr class="data-row">
                                        <td>{{ $key + 1 }}</td>
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
        <div class="modal-dialog modal-md odal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add List</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('baseSettingsStore') }}" method="post" id="frmAchiever"
                    class="form-horizontal  form-material">
                    @csrf
                    <input type="hidden" name="type" value="achiever">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Value</label>
                            <div class="col-sm-12 col-md-8">
                                <input class="form-control" type="text" required name="value">
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
                    <h4 class="modal-title">Edit List</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form action="{{ route('baseSettingsUpdate') }}" method="post" id="frmEditAchiever"
                    class="form-horizontal  form-material">
                    @csrf
                    <input type="hidden" name="id" id="modal-input-id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Value</label>
                            <div class="col-sm-12 col-md-8">
                                <input class="form-control" type="text" id="modal-input-name" required name="value">
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
                var name = row.children(".name").text();
                console.log(id);
                // fill the data in the input fields
                $("#modal-input-id").val(id);
                $("#modal-input-name").val(name);
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
                            url: '{{ route('baseSettingsDelete', ':id') }}'.replace(':id',
                                itemId),
                            type: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: '"Deleted!", "Your file has been deleted"',
                                    icon: 'error',
                                });
                                // $('#myTable').DataTable().ajax.reload();
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

        })
    </script>
@endsection
