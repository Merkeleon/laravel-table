<div class="form__element form__element_filter_text">
    <label>{{$label}}</label>
    <div class="form__element-container">
        <input @foreach ($attributes as $k => $v) {{ $k }}="{{ $v }}" @endforeach class="form-control" type="text"
        value="{{$value}}" name="f_{{$name}}"/>
    </div>
</div>
