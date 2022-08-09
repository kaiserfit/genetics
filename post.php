<?php


// switch ($_SERVER['HTTP_ORIGIN']) {
//     case 'https://kaiserbody.com': case 'https://kaiserbody-com.cbsplit.com':
//     header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//     header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//     header('Access-Control-Max-Age: 1000');
//     header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
//     break;
//     case 'https://kaisercoach.com':
//       header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//       header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//       header('Access-Control-Max-Age: 1000');
//       header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
//       break;
//       case 'https://queenformula.net':
//         header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//         header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//         header('Access-Control-Max-Age: 1000');
//         header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
//         break;
//         case 'https://kaizerfit.com':
//           header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//           header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//           header('Access-Control-Max-Age: 1000');
//           header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
//           break;
//   }
header('Access-Control-Allow-Origin: *');
          header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          header('Access-Control-Max-Age: 1000');
          header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
         
          require_once('stripe-php-8.9.0/init.php');
 

  $input = json_decode(file_get_contents('php://input'), true);
  // $input = file_get_contents('php://input');




// customer id (from stripe)
// full name
// email
// phone
// generated string password
// id of product ordered
// price
// unique event id
// payment intent charge id
// payment intent id 


$prices = [
  '294.00'=> '21142',
  '177.00'=> '21141',
  '63.00'=> '21140',
];






  $customerid = $input["customerId"];
  $fname = $input["customerName"];
  $email = $input['customerEmail'];
  $phone = $input["customerPhone"];
  $sku = $input["productId"];
  $phash = $input["userPassword"];
  $orderid = $input["paymentIntentId"];
  $charge_id = $input["paymentChargesId"];
  $orderbump = $input["limitedOffer"];
  date_default_timezone_set('Australia/Sydney');
  $cur_date = date('Y-m-d');

 
  $price1 = array_keys($prices, $sku);

  $price = (string)$price1[0];
  $botCount = getBotCount($sku);

  // retrieveCustomer($email);
    
         
       
            $subid = createSub($customerid, $orderbump, $orderid);
            $paymentMethodID = getPaymentMethod($customerid);
            updateSub($subid, $paymentMethodID);
            create_customer_local($customerid, $cur_date, $phash, $fname,$email,$phone);
            create_order($customerid,$charge_id, $orderid, $sku, $cur_date);
            saveDigital($orderid, $charge_id, $price);
            saveSupp($orderid, $charge_id, $sku, $botCount);
           

      

            
        

    

   
  
   

  function retrieveCustomer($email){

    ##get customer details from stripe
    $stripe = new \Stripe\StripeClient(
      // 'sk_live_rQ1AeTlZhy0glKeBEN6idfkm'
      'sk_live_rQ1AeTlZhy0glKeBEN6idfkm'
    );


    $obj = $stripe->customers->all([
      'limit' => 1,
      'email' => $email
    ]);
    // var_dump($obj);
    // exit();
    // $res = json_decode($obj);
    // echo $res;
 
    if (count($obj["data"])>0){
      echo json_encode($obj);
    } else {
      echo json_encode(array("aser"=>"asdf"));
     
    }
   
  }


  function getPaymentMethod($customerid){
    $stripe = new \Stripe\StripeClient(
      'sk_live_rQ1AeTlZhy0glKeBEN6idfkm'
    );
   $obj = $stripe->customers->allPaymentMethods(
      $customerid,
      ['type' => 'card']
    );

    return $obj->id;
  }


  function getBotCount($sku){
  
    $count = 0;
    switch ($sku) {
      case "21140":
        $count = 1;
        break;
        case "21141":
          $count = 3;
          break;
          case "21142":
            $count = 6;
            break;
            case "21475":
              $count = 1;
              break;
              case "21480":
                $count = 3;
                break;
                case "21487":
                  $count = 6;
                  break;
                  case "21476":
                    $count = 1;
                    break;
                    case "21481":
                      $count = 3;
                      break;
                      case "21490":
                        $count = 6;
                        break;
                        case "21477":
                          $count = 1;
                          break;
                          case "21482":
                            $count = 3;
                            break;
                            case "21491":
                              $count = 6;
                              break;
    
        default:
        break;
    }
    
    return $count;
  }





  ############ LOCAL DATABASE FUNCTIONS #############

  function dd($customerid, $orderid) { //add Divine Desserts to customer if there is an orderbump
    $servername = "localhost";
    $username = "paykaiserfitapp";
    $password = "7iUH3iN01KeeLPD";
    $dbname = "paykaiserfitapp";
    date_default_timezone_set('Australia/Sydney');
    $cdate = date('Y-m-d');
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "INSERT INTO `user_recipe_purchase`(`customerid`, `recipeid`, `amount`, `payment_date`, `orderid`) VALUES ('$customerid', 11, 29, '$cdate', '$orderid' )";
    $conn->query($sql);
    $conn->close();
  }


  function createSub($customerid, $orderbump, $orderid) { ##createSub
          //create the subscription for kaisercoach
     
    $stripe = new \Stripe\StripeClient(
      'sk_live_rQ1AeTlZhy0glKeBEN6idfkm'
    );

    if ($orderbump){
      dd($customerid, $orderid);
     $sub = $stripe->subscriptions->create([
        'customer' =>$customerid,
        'items' => [
       
          ['price' => 'price_1JfHjQGEnOOm6AiQSwDAI7u1'],//live
          // ['price' => 'price_1Jh0UHGEnOOm6AiQ8YRTwL1q'],//test
        ],
        'trial_period_days'=> 30
      ]);
      return $sub->id;
    } else {
      date_default_timezone_set('UTC');
      $date = new DateTime();
      $date->add(new DateInterval('P30D'));
      $ts = $date->getTimestamp(); //this is one month after sub
      $sub = $stripe->subscriptions->create([
        'customer' =>$customerid,
        'items' => [
          // ['price' => $sub],//live
          ['price' => 'price_1JfHjQGEnOOm6AiQSwDAI7u1'],//live
          // ['price' => 'price_1Jh0UHGEnOOm6AiQ8YRTwL1q'],//test
        ],
        'cancel_at'=>$ts,
        'trial_period_days'=> 30
      ]);

      return $sub->id;
    }  
    

  }


  function updateSub($subid, $paymentMethodID){
    $stripe = new \Stripe\StripeClient(
      'sk_live_rQ1AeTlZhy0glKeBEN6idfkm'
    );
    $stripe->subscriptions->update(
      $subid,
      [ 'default_payment_method' => $paymentMethodID]
    );
  }



  function create_customer_local($customerid, $cur_date, $phash, $fname,$email,$phone){ //create customer in local database
             $servername = "localhost";
           $username = "paykaiserfitapp";
           $password = "7iUH3iN01KeeLPD";
           $dbname = "paykaiserfitapp";
           $conn = new mysqli($servername, $username, $password, $dbname);
           $user_tok = null;
   
   
             $sql = "INSERT INTO `tbl_customers`(`customer_id`, `creation_date`, `c_name`, `c_email`, `c_phone`, `user_hash`) VALUES ('$customerid','$cur_date','$fname','$email','$phone','$phash')";
            if ($conn->query($sql)){
              $user_tok = 1;
            } else {
              echo $conn->error;
            }
             
             sendToKaiserCOach($email, $customerid, $cur_date, $phash, $fname, $phone);
           
             $conn->close(); 
   
       }

       function sendToKaiserCOach($email, $customerid, $cur_date, $phash, $fname, $phone){
        $url = 'https://kaisercoach.com/api/createUser.php';
       
        // what post fields?
        $fields = array(
           'emails' => $email,
           'customer' => $customerid,
           'token' => '',
           'pdate' => $cur_date,
           'phash' => $phash,
           'cname' => $fname,
           'phone' => $phone,
           'street' => '',
           'town' => '',
           'pcode' => '',
           'country' => '',
           'state' => '',
        );
        
        // build the urlencoded data
        $postvars = http_build_query($fields);
        
        // open connection
        $ch = curl_init();
        
        // set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
       
        
        // execute post
        $result = curl_exec($ch);
        
        //return $result;
        // close connection
        curl_close($ch);
    
    
      }


      function create_order($customerid,$charge_id, $orderid, $sku, $cur_date){//create order in local database
        $servername = "localhost";
        $username = "paykaiserfitapp";
        $password = "7iUH3iN01KeeLPD";
        $dbname = "paykaiserfitapp";
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "INSERT INTO `tbl_orders`(`customer_id`, `order_number`,`charge_id`, `productid`, `order_date`, `page_name`) VALUES ('$customerid','$orderid', '$charge_id','$sku','$cur_date','checkout')";
        $conn->query($sql);
        $sql2 = "INSERT INTO `orders_table`(`order_date`,`is_funnel`, `customerid`, `order_number`) VALUES ('$cur_date',1,'$customerid', '$orderid')";      
        $conn->query($sql2);
       
               $conn->close(); 
      }


      function saveDigital($orderid, $charge_id, $price){ //save the digital product order detail on local database
        #productPrice is stripe_sku on local database
        $servername = "localhost";
        $username = "paykaiserfitapp";
        $password = "7iUH3iN01KeeLPD";
        $dbname = "paykaiserfitapp";
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql3 = "INSERT INTO `order_details`(`order_number`, `stripe_order_number`, `stripe_charge`, `skuid`, `stripe_sku`, `order_qty`, `shippable`) VALUES ('$orderid','$orderid','$charge_id','0','price_1JfHjQGEnOOm6AiQSwDAI7u1',1,0)";
        $conn->query($sql3);
        $conn->close(); 
      
      }

      function saveSupp($orderid, $charge_id, $sku, $botCount){ //save supplement product order detail on the local database set as shippable
        #productPrice is stripe_sku on local database
        $servername = "localhost";
        $username = "paykaiserfitapp";
        $password = "7iUH3iN01KeeLPD";
        $dbname = "paykaiserfitapp";
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql = "INSERT INTO `order_details`(`order_number`, `stripe_order_number`, `stripe_charge`, `skuid`, `stripe_sku`, `order_qty`, `bot_qty`, `shippable`) VALUES ('$orderid','$orderid','$charge_id','17039-123','$sku',1, $botCount,1)";
        $conn->query($sql);
        $conn->close(); 
      
      }