<x-admission::layouts.layout-registration-steps :$title :$subtitle>
    <form role="form" id="form-data" method="post" enctype="multipart/form-data">
        @csrf @method('POST')

        <div class="table-max table-max_absolute">
            <table class="table-spmb">
                <thead>
                <tr>
                    <th>Syarat</th>
                    <th>Dokumen</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="desc-table">
                                Pas Foto Terbaru<span class="text-danger">*</span>
                            </div>
                        </td>
                        <td>
                            <img id="thumbnail-preview" width="50" onclick="goImageModal()" style="display: none; cursor: pointer">
                            <img id="thumbnail-img" width="50" src="{{ asset('images/nopic.png') }}" onclick="goImageModal()" style="cursor: pointer">
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</x-admission::layouts.layout-registration-steps>
