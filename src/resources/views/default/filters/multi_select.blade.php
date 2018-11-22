<div class="form__element form__element_filter_select  form__element--wide @if($error) form__element_error @endif" >
    <label>{{$label}}</label>
    <div class="form__element-container">
        <select name="f_{{$name}}[]" multiple class="form-control" data-toggle="selectize">
            <option></option>
            @foreach ($options as $key => $option)
                <option value="{{$key}}" @if ($value && in_array($key, $value)) selected @endif>{{$option}}</option>
            @endforeach
        </select>
        <div class="form__feedback">{{$error}}</div>
    </div>
</div>
