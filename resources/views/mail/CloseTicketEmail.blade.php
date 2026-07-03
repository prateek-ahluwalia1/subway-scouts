<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
       <style>
        *,
*::after,
*::before{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

:root{
    --blue-color: #0c2f54;
    --dark-color: #535b61;
    --white-color: #fff;
}

/* text colors */


.text-end{
    text-align: right;
}

.text-start{
    text-align: left;
}
.text-bold{
    font-weight: 700;
}
/* hr line */
.hr{
    height: 2px;
    background-color: rgba(0, 0, 0, 0.1);
    margin-bottom: 5px;
}
/* border-bottom */
.border-bottom{
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

body{
    font-family: 'Poppins', sans-serif;
    color: var(--dark-color);
    font-size: 14px;
}
.invoice-wrapper{
    min-height: 100vh;
    background-color: rgba(0, 0, 0, 0.1);
    padding-top: 20px;
    padding-bottom: 20px;
}
.invoice{
    max-width: 850px;
    margin-right: auto;
    margin-left: auto;
    background-color: var(--white-color);
    padding: 70px;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    min-height: 920px;
}
.invoice-head-top-left img{
    width: 130px;
}
.invoice-head-top-right h3{
    font-weight: 500;
    font-size: 27px;
    color: var(--blue-color);
}
.invoice-head-middle, .invoice-head-bottom{
    padding: 16px 0;
}
.invoice-body{
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    overflow: hidden;
   
}


.invoice-foot{
    padding: 30px 0;
}
.invoice-foot p{
    font-size: 12px;
}


.invoice-head-top, .invoice-head-middle, .invoice-head-bottom{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    padding-bottom: 10px;
}

@media screen and (max-width: 992px){
    .invoice{
        padding: 40px;
    }
}

@media screen and (max-width: 576px){
    .invoice-head-top, .invoice-head-middle, .invoice-head-bottom{
        grid-template-columns: repeat(1, 1fr);
    }
    .invoice-head-bottom-right{
        margin-top: 12px;
        margin-bottom: 12px;
    }
    .invoice *{
        text-align: left;
    }
    .invoice{
        padding: 28px;
    }
}

.overflow-view{
    overflow-x: scroll;
}
.invoice-body{
    min-width: 600px;
}

       </style>
    </head>
    <body>

        <div class = "invoice-wrapper">
            <div class = "invoice">
                <div class = "invoice-container">
                    <div class = "invoice-head">
                        <div class = "invoice-head-top">
                            <div class = "invoice-head-top-left text-start">
                                <img src ="https://app.247staffingsolutions.com.au/assets/images/logo/scouts.png" alt="logo">
                            </div>
                            <div class = "invoice-head-top-right text-end">
                                <h3>Support</h3>
                            </div>
                        </div>
                        <div class = "hr"></div>
                        <div class = "invoice-head-middle">
                            
                            <div class = "invoice-head-middle-left text-start">
                                <h2 style="margin-bottom: 10px;">{{ $data['subject'] }}</h2>  
                                <p style="margin-bottom: 4px;"><span class = "text-bold"  style="padding-right: 30px;">Name: </span>{{ $data['name'] }}</p>
                                <p style="margin-bottom: 4px;"><span class = "text-bold" style="padding-right: 40px;">Date:</span>{{ $data['date'] }}</p>
                                <p style="margin-bottom: 4px;"><span class = "text-bold" style="padding-right: 11px;">Ticket Status:</span>{{ $data['status'] }}</p>
                            </div>
                            <div class = "invoice-head-middle-right text-end">
                                <p><spanf class = "text-bold"></span></p>
                                    <p><span class = "text-bold" style="font-size: 20px;">Ticket # </span>000{{ $data['ticket'] }}</p>

                            </div>
                        </div>
                        <div class = "hr"></div>
                       
                    </div>
                    <div >
                        <div class = "invoice-body">
                            
                        <div class="card-body" style="margin-top: 20px; padding: 20px; text-align: center;">
                            {{ $data['message'] }}
                        </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>

        <script src = "script.js"></script>
    </body>
</html>