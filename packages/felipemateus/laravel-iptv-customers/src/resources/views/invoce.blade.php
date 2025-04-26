<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<style>
body {
  background: #EEE;
  /* font-size:0.9em !important; */
}

.invoice {
  width: 970px !important;
  margin: 50px auto;
}
.invoice .invoice-header {
  padding: 25px 25px 15px;
}
.invoice .invoice-header h1 {
  margin: 0;
}
.invoice .invoice-header .media .media-body {
  font-size: 0.9em;
  margin: 0;
}
.invoice .invoice-body {
  border-radius: 10px;
  padding: 25px;
  background: #FFF;
}
.invoice .invoice-footer {
  padding: 15px;
  font-size: 0.9em;
  text-align: center;
  color: #999;
}

.logo {
  max-height: 70px;
  border-radius: 10px;
}

.dl-horizontal {
  margin: 0;
}
.dl-horizontal dt {
  float: left;
  width: 80px;
  overflow: hidden;
  clear: left;
  text-align: right;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.dl-horizontal dd {
  margin-left: 90px;
}

.rowamount {
  padding-top: 15px !important;
}

.rowtotal {
  font-size: 1.3em;
}

.colfix {
  width: 12%;
}

.mono {
  font-family: monospace;
}
</style>
</head>
<body>
<div class="container invoice">
  <div class="invoice-header">
    <div class="row">
      <div class="col-md-8">
        <h1>Invoice <small>With Credit</small></h1>
        <h4 class="text-muted">NO: {{ $invoce->id}} | Date: {{ date('d/m/Y',$invoce->due_date) }}</h4>
      </div>
      <div class="col-md-4">
        <div class="media">
          <div class="media-left">
            <img class="media-object logo" src="https://dummyimage.com/70x70/000/fff&text=ACME" />
          </div>
          <ul class="media-body list-unstyled">
            <li><strong>Acme Corporation</strong></li>
            <li>Software Development</li>
            <li>Field 3, Moon</li>
            <li>info@acme.com</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="invoice-body">
    <div class="row">
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Company Details</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
             @foreach($ConfigData  as  $value)
              <dt>{{ __($value['name']) }}</dt>
              <dd>{{  $value['val'] }}</dd>
             @endforeach
             <!--
              <dt>Business Name</dt>
              <dd><strong>Acme Corporation</strong></dd>
              <dt>Industry</dt>
              <dd>Software Development</dd>
              <dt>Address</dt>
              <dd>Field 3, Moon</dd>
              <dt>Phone</dt>
              <dd>123.4456.4567</dd>
              <dt>Email</dt>
              <dd>mainl@acme.com</dd>
              <dt>Tax NO</dt>
              <dd class="mono">123456789</dd>
              <dt>Tax Office</dt>
              <dd>A' Moon</dd>-->
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Customer Details</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
              <dt>Name</dt>
              <dd>{{ $invoce->customer->name}}</dd>
              <dt>Industry</dt>
              <dd>{{ $invoce->customer->industry}}</dd>
              <dt>Address</dt>
              <dd>{{ $invoce->customer->address}}</dd>
              <dt>Phone</dt>
              <dd>{{ $invoce->customer->phone}}</dd>
              <dt>Email</dt>
              <dd>{{ $invoce->customer->email}}</dd>
              <dt>Tax NO</dt>
              <dd class="mono">{{ $invoce->customer->tax_no}}</dd>
              <dt>&nbsp;</dt>
              <dd>&nbsp;</dd>
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Services / Products</h3>
      </div>
      <table class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th>Item / Details</th>
            <th class="text-center colfix">Cost</th>
            <th class="text-center colfix">Discount</th>
            <th class="text-center colfix">Tax</th>
            <th class="text-center colfix">Total</th>
          </tr>
        </thead>
        <tbody>

        @foreach($services as $item)
          <tr>
            <td>
              {{ $item['service'] }}
              <br>
              <small class="text-muted">{{  $item['service_type'] }}</small>
            </td>
            <td class="text-right">
              <span class="mono"> {{ number_format($item['price'], 2, ',', ' ') }}</span>
              <br>
              <small class="text-muted">Before Tax</small>
            </td>
            <td class="text-right">
              <span class="mono">- {{ number_format($item['discont'], 2, ',', ' ') }}</span>
              <br>
              <small class="text-muted">-</small>
            </td>
            <td class="text-right">
              <span class="mono">+ {{ number_format($item['tax'], 2, ',', ' ') }}</span>
              <br>
              <small class="text-muted">VAT: {{ $item['tax_porcent'] }}%</small>
            </td>
            <td class="text-right">
              <strong class="mono"> {{ number_format($item['total'], 2, ',', ' ') }} </strong>
              <br>
              <small class="text-muted mono"> {{ number_format($item['subtotal'], 2, ',', ' ') }} </small>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="panel panel-default">
      <table class="table table-bordered table-condensed">
        <thead>
          <tr>
            <td class="text-center col-md-1">Sub Total</td>
            <td class="text-center col-md-1">Discount</td>
            <td class="text-center col-md-1">Total</td>
            <td class="text-center col-md-1">Tax</td>
            <td class="text-center col-md-1">Final</td>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th class="text-center rowtotal mono">{{ number_format($subtotal, 2, ',', ' ') }}</th>
            <th class="text-center rowtotal mono">- {{ number_format($totalDiscount, 2, ',', ' ') }}</th>
            <th class="text-center rowtotal mono"> {{ number_format($total, 2, ',', ' ') }} </th>
            <th class="text-center rowtotal mono">+ {{ number_format($totalTax, 2, ',', ' ') }} </th>
            <th class="text-center rowtotal mono"> {{ number_format($final, 2, ',', ' ') }} </th>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="row">
      <div class="col-md-7">
        <div class="panel panel-default">
          <div class="panel-body">
            <i>Comments / Notes</i>
            <hr style="margin:3px 0 5px" /> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Odit repudiandae numquam sit facere blanditiis, quasi distinctio ipsam? Libero odit ex expedita, facere sunt, possimus consectetur dolore, nobis iure amet vero.
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Payment Method</h3>
          </div>
          <div class="panel-body">
            <p>For your convenience, you may deposite the final ammount at one of our banks</p>
            <ul class="list-unstyled">
                @foreach($GatewaysList as $item)
                    <li>
                    {{  $item->class_model::form($final, $invoce->id)  }}
                    </li>
                @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>

  </div>
  <div class="invoice-footer">
    Thank you for choosing our services.
    <br /> We hope to see you again soon
    <br />
    <strong>~ACME~</strong>
  </div>
</div>
</body>
</html>
