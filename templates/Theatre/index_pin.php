<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Routing\Router;

?>
<style>
    body {
        /*background: #3498db;*/
    }

    #PINform input:focus,
    #PINform select:focus,
    #PINform textarea:focus,
    #PINform button:focus {
        outline: none;
    }

    #PINform {
        background: #ededed;
        border-radius: 10px;
        width: 350px;
        /*position: absolute;*/
        /*height: 400px;*/
        left: 50%;
        top: 50%;
        margin-top: 0;
        padding: 15px;
        -webkit-box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
        -moz-box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
        box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
    }

    #PINbox {
        background: #ededed;
        margin: 1.5%;
        margin-top: 15px;
        width: 92%;
        font-size: 3em;
        text-align: center;
        /*border: 2px solid #d5d5d5;*/
        border-width: 2px;
        border-style: solid;
        border-radius: 10px;
    }

    .border-red {
        border-color: red;
    }

    .border-grey {
        border-color: #d5d5d5;
    }

    .PINbutton {
        background: #ededed;
        color: #7e7e7e;
        border: none;
        /*background: linear-gradient(to bottom, #fafafa, #eaeaea);
          -webkit-box-shadow: 0px 2px 2px -0px rgba(0,0,0,0.3);
             -moz-box-shadow: 0px 2px 2px -0px rgba(0,0,0,0.3);
                  box-shadow: 0px 2px 2px -0px rgba(0,0,0,0.3);*/
        border-radius: 50%;
        font-size: 1.3em;
        text-align: center;
        width: 50px;
        height: 50px;
        margin: 7px 20px;
        padding: 0;
    }

    .clear, .enter {
        font-size: 1em;
    }

    .PINbutton:hover {
        box-shadow: #506CE8 0 0 1px 1px;
    }

    .PINbutton:active {
        background: #506CE8;
        color: #fff;
    }

    .clear:hover {
        box-shadow: #ff3c41 0 0 1px 1px;
    }

    .clear:active {
        background: #ff3c41;
        color: #fff;
    }

    .enter:hover {
        box-shadow: #47cf73 0 0 1px 1px;
    }

    .enter:active {
        background: #47cf73;
        color: #fff;
    }

    .shadow {
        -webkit-box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
        -moz-box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
        box-shadow: 0px 5px 5px -0px rgba(0, 0, 0, 0.3);
    }

    .display-3 {
        font-size: 4rem;
        font-weight: 300;
        line-height: 1;
    }

    .lead {
        font-size: 1.7rem;
        font-weight: 300;
    }

</style>


<div class="row">
    <div class="m-auto">
        <p class="display-5 mt-3 mb-1 text-center"><?php echo __('Hello Stranger') ?></p>
        <p class="fs-1 mt-1 mb-3 text-center"><?php echo __('Please enter your PIN Code') ?></p>
    </div>
</div>

<div class="row">
    <div class="m-auto">
        <div id="PINcode" class="m-auto text-center">
            <form action="" method="post" name="PINform" id="PINform" autocomplete="off" class="m-auto">
                <label for="PINbox"></label>
                <input id="PINbox" class="border-grey" type="password" value="" name="PINbox" disabled="disabled"
                       maxlength="12"><br>
                <input type="button" class="PINbutton number" name="1" value="1" id="N1">
                <input type="button" class="PINbutton number" name="2" value="2" id="N2">
                <input type="button" class="PINbutton number" name="3" value="3" id="N3"><br>
                <input type="button" class="PINbutton number" name="4" value="4" id="N4">
                <input type="button" class="PINbutton number" name="5" value="5" id="N5">
                <input type="button" class="PINbutton number" name="6" value="6" id="N6"><br>
                <input type="button" class="PINbutton number" name="7" value="7" id="N7">
                <input type="button" class="PINbutton number" name="8" value="8" id="N8">
                <input type="button" class="PINbutton number" name="9" value="9" id="N9"><br>
                <input type="button" class="PINbutton clear" name="-" value="Clear" id="-">
                <input type="button" class="PINbutton number" name="0" value="0" id="N0"">
                <input type="button" class="PINbutton enter" name="+" value="Enter" id="+">
            </form>
        </div>
    </div>
</div>

<?php
$this->append('viewPluginScripts');
$this->end();

$this->append('viewCustomScripts');
?>
<script>
    /**
     * @var homeUrl
     * @var csrfToken
     */
    $(document).ready(function () {

        $('.PINbutton.number').on('click.PinNumber', function () {
            addNumber(this);
        })

        $('.PINbutton.clear').on('click.PinClear', function () {
            clearForm(this);
        })

        $('.PINbutton.enter').on('click.PinEnter', function () {
            submitForm(this);
        })

        function addNumber(e) {
            var pinBox = $("#PINbox");
            var pin = pinBox.val();
            pinBox.val(pin + e.value).removeClass('border-red').addClass('border-grey');
        }

        function clearForm(e) {
            $("#PINbox").val("").removeClass('border-red').addClass('border-grey');
        }

        function submitForm(e) {
            var pinBox = $("#PINbox");
            var pin = pinBox.val();

            if (pin === "") {
                alert("Enter a PIN");
            } else {
                submitPin(pin);
            }
        }

        function submitPin(pin) {
            var targetUrl = homeUrl + 'theatre/pin/';
            var formData = new FormData();
            formData.append("pin", pin);

            $.ajax({
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                type: "POST",
                url: targetUrl,
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 10000,

                success: function (response) {
                    if (response === true) {
                        window.location.replace(homeUrl + 'theatre');
                    } else {
                        $('#PINbox').removeClass('border-grey').addClass('border-red');
                    }
                },
                error: function (e) {
                    //alert("An error occurred: " + e.responseText.message);
                    console.log(e);
                }
            })
        }

    });
</script>
<?php
$this->end();
?>
