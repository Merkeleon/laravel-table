<div class="table-filter">
    <form method="get" class="form-vertical">
        @if(count($filters))
            <div class="table-filter__form form">
                @foreach($filters as $filter)
                    {!! $filter->render() !!}
                @endforeach
            </div>
            <input type="hidden" name="orderField" value="{{$orderField}}">
            <input type="hidden" name="orderDirection" value="{{$orderDirection}}">
        @endif
        @if(count($filters) || count($exporters))
        <div class="table-filter__actions">
            <div class="table-filter__actions-filter">
                @if(count($filters))
                    <input type="submit" class="btn btn-success" value="{{ trans('table::table.button.filter') }}">
                    @if($filtersAreActive)
                        <a class="btn btn-warning"
                           href="?orderField={{$orderField}}&orderDirection={{$orderDirection}}">{{ trans('table::table.button.reset') }}</a>
                    @endif
                @endif
            </div>
            <div class="table-filter__actions-exporters">
                @if(count($rows))
                    @foreach($exporters as $key => $exporter)
                        <a class="btn btn-info" @if ($exporter->isTargetBlank()) target="_blank" @endif
                           href="?{{http_build_query(array_merge(request()->all(), ['export_to' => $key]))}}">
                            {{ $exporter->getLabel() }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        @endif
    </form>
</div>

<div class="table-content">
    <table @foreach ($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach>
        <thead>
            <tr>
                @foreach($columns as $key => $column)
                    <th>
                        @if(in_array($key, $sortables))
                            @if($orderField == $key)
                                <a class="table_sorting" href="?{{http_build_query(array_merge(request()->all(), [ 'orderField' => $key, 'orderDirection' => $orderDirection == 'asc' ? 'desc' : 'asc']))}}">
                                    {{$column}}
                                    @if($orderDirection == 'asc')
                                        <span class="table-arrow-up"></span>
                                        <span class="table-arrow-down" style="visibility: hidden;"></span>
                                    @else
                                        <span class="table-arrow-down"></span>
                                        <span class="table-arrow-up" style="visibility: hidden;"></span>
                                    @endif
                                </a>
                            @else
                                <a class="table_sorting" href="?{{http_build_query(array_merge(request()->all(), [ 'orderField' => $key, 'orderDirection' => 'asc']))}}">
                                    {{$column}}
                                    <span class="table-arrow-up"></span><span class="table-arrow-down"></span>&nbsp;
                                </a>
                            @endif
                        @else
                            {{$column}}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $rowData)
                @include($rowViewPath, [
                    'data' => $rowData,
                    'columns' => $columns,
                    'orderField' => $orderField
                ])
            @empty
                <tr>
                    <td colspan="{{count($columns)}}" style="text-align: center">
                        {{ trans('table::table.row.empty') }}
                    </td>
                </tr>
            @endforelse
            @if(count($totals))
                <tr class="ctable-total-heading">
                    @foreach($columns as $key => $column)
                        <td>
                            @if(array_get($totals, $key))
                            {{$column}}&nbsp;{{trans('table::table.total.'.array_get($totals, $key.'.type'))}}
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr class="ctable-total-content">
                    @foreach($columns as $key => $column)
                        <td>
                            {{array_get($totals, $key.'.total')}}
                        </td>
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="table-pagination">
    @include('table::default.pagination', ['paginator' => $pagination])
</div>