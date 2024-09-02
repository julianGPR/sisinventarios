<?php
$qry = $conn->query("SELECT b.*,s.name as supplier,p.po_code FROM back_order_list b inner join supplier_list s on b.supplier_id = s.id inner join purchase_order_list p on b.po_id = p.id  where b.id = '{$_GET['id']}'");
if ($qry->num_rows > 0) {
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
}
?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Información de Orden Devuelta - <?php echo $bo_code ?></h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">Código</label>
                    <div><?php echo isset($po_code) ? $po_code : '' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id" class="control-label text-info">Proveedor</label>
                        <div><?php echo isset($supplier) ? $supplier : '' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="control-label text-info">Codigo</label>
                    <div><?php echo isset($bo_code) ? $bo_code : '' ?></div>
                </div>
            </div>
            <h4 class="text-info">Órdenes</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="10%">
                    <col width="30%">
                    <col width="25%">
                    <col width="25%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">CANT</th>
                        <th class="text-center py-1 px-2">Unidad</th>
                        <th class="text-center py-1 px-2">Producto</th>
                        <th class="text-center py-1 px-2">Costo</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    $qry = $conn->query("SELECT b.*,i.name,i.description FROM `bo_items` b inner join item_list i on b.item_id = i.id where b.bo_id = '{$id}'");
                    while ($row = $qry->fetch_assoc()) :
                        $total += $row['total']
                    ?>
                        <tr>
                            <td class="py-1 px-2 text-center"><?php echo number_format($row['quantity'], 2) ?></td>
                            <td class="py-1 px-2 text-center"><?php echo ($row['unit']) ?></td>
                            <td class="py-1 px-2">
                                <?php echo $row['name'] ?> <br>
                                <?php echo $row['description'] ?>
                            </td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['price']) ?></td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['total']) ?></td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Sub Total</th>
                        <th class="text-right py-1 px-2 sub-total"><?php echo number_format($total, 2)  ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Descuento <?php echo isset($discount_perc) ? $discount_perc : 0 ?>%</th>
                        <th class="text-right py-1 px-2 discount"><?php echo isset($discount) ? number_format($discount, 2) : 0 ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Impuesto <?php echo isset($tax_perc) ? $tax_perc : 0 ?>%</th>
                        <th class="text-right py-1 px-2 tax"><?php echo isset($tax) ? number_format($tax, 2) : 0 ?></th>
                    </tr>
                    <tr>
                        <th class="text-right py-1 px-2" colspan="4">Total</th>
                        <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount, 2) : 0 ?></th>
                    </tr>
                </tfoot>
            </table>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="text-info control-label">Observaciones</label>
                        <p><?php echo isset($remarks) ? $remarks : 'N/A' ?></p>
                    </div>
                </div>
                <?php if ($status > 0) : ?>
                    <div class="col-md-6">
                        <span class="text-info"><?php echo ($status == 2) ? "Recibida" : "Parcialmente Recibida" ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-success" type="button" id="print">Imprimir</button>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url . '/admin?page=back_order' ?>">Volver</a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center qty">
            <span class="visible"></span>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
        </td>
        <td class="py-1 px-2 text-center unit">
        </td>
        <td class="py-1 px-2 item">
        </td>
        <td class="py-1 px-2 text-right cost">
        </td>
        <td class="py-1 px-2 text-right total">
        </td>
    </tr>
</table>
<script>
    $(function() {
        $('#print').click(function() {
            start_loader()
            var _el = $('<div>')
            var _head = $('head').clone()
            _head.find('title').text("Información Orden de Compra - Vista de Impresión")
            var p = $('#print_out').clone()
            p.find('tr.text-light').removeClass("text-light bg-navy")
            _el.append(_head)
            _el.append('<div class="d-flex justify-content-center">' +
                '<div class="col-1 text-right">' +
                '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />' +
                '</div>' +
                '<div class="col-10">' +
                '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>' +
                '<h4 class="text-center">Volver</h4>' +
                '</div>' +
                '<div class="col-1 text-right">' +
                '</div>' +
                '</div><hr/>')
            _el.append(p.html())
            var nw = window.open("", "", "width=1200,height=900,left=250,location=no,titlebar=yes")
            nw.document.write(_el.html())
            nw.document.close()
            setTimeout(() => {
                nw.print()
                setTimeout(() => {
                    nw.close()
                    end_loader()
                }, 200);
            }, 500);
        })
    })
</script>