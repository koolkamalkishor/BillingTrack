<?php

namespace BT\DataTables;

use BT\Modules\Payments\Models\Payment;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class PaymentsTrashDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable->addColumn('action', 'utilities._actions')
           ->editColumn('invoice.number', function (Payment $payment) {
                return '<a href="/invoices/' . $payment->invoice_id . '/edit">' . $payment->invoice->number . '</a>';
            })
            ->editColumn('id', function (Payment $payment) {
                return '<input type="checkbox" class="bulk-record" data-id="' . $payment->id . '">';
            })
            ->editColumn('client.name', function (Payment $payment) {
                return '<a href="clients/'.$payment->client->id .'/edit">'. $payment->client->name .'</a></td>';
            })
            ->orderColumn('formatted_paid_at', 'paid_at $1')
            ->orderColumn('formatted_amount', 'amount $1')
            ->rawColumns([ 'invoice.number','client.name', 'action', 'id']);
    }


    /**
     * Get query source of dataTable.
     *
     * @param \BT\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Payment $model)
    {
        return $model->has('client')->has('invoice')->with('client', 'invoice','paymentMethod')->onlyTrashed();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->ajax(['data' => 'function(d) { d.table = "payments"; }'])
            ->addAction(['width' => '80px'])
            //->parameters($this->getBuilderParameters())
            ->parameters([
                'order' => [1, 'desc'],
                'lengthMenu' => [
                    [ 10, 25, 50, 100, -1 ],
                    [ '10', '25', '50', '100', 'All' ]
                ],
                ]);
    }

    /**
     * Get columns.
     *
     * @return array
     * TODO problems with eloquent using getter on nested relation for ordering/search
     */
    protected function getColumns()
    {
        return [
            'id' =>
                [   'name'       => 'id',
                    'data'       => 'id',
                    'orderable'  => false,
                    'searchable' => false,
                    'printable'  => false,
                    'exportable' => false,
                    'class'      => 'bulk-record',
                ],
            'paid_at' => [
                'title' => trans('bt.payment_date'),
                'data' => 'formatted_paid_at',
                'searchable' => false,
            ],
            'invoice_number' => [
                    'title' => trans('bt.invoice'),
                    'data'       => 'invoice.number',
                ],
            'invoice_date' => [
                'name' => 'invoice.invoice_date',
                'title' => trans('bt.invoice_date'),
                'data'       => 'invoice.formatted_invoice_date',
                'orderable'  => true,
                'searchable' => false,
            ],
            'client_name'   => [
                'title' => trans('bt.client'),
                'data'       => 'client.name',
            ],
            'invoice_summary'   => [
                'title' => trans('bt.summary'),
                'data'       => 'invoice.summary',
            ],
            'amount'   => [
                'title' => trans('bt.amount'),
                'data'       => 'formatted_amount',
                'searchable' => false,
            ],
            'payment_method'  => [
                'title' => trans('bt.payment_method'),
                'name' => 'paymentMethod.name',
                'data' => 'payment_method.name',
                'searchable' => false,
            ],
            'note'    => [
                'title' => trans('bt.note'),
                'data'       => 'note',
            ],

        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Payments_' . date('YmdHis');
    }
}
