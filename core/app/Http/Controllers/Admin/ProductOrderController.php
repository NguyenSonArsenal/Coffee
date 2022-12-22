<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Mail;
use App\Models\BasicExtended;
use App\Models\User;
use App\Models\Language;
use Session;


use App\Models\PaymentGateway;

class ProductOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('setlang');
    }

    public function all(Request $request)
    {
        $search = $request->search;
        $data['orders'] =
        ProductOrder::when($search, function ($query, $search) {
            return $query->where('order_number', $search);
        })
        ->orderBy('id', 'DESC')->paginate(10);

        return view('admin.product.order.index', $data);
    }

    public function pending(Request $request)
    {
        $search = $request->search;
        $data['orders'] = ProductOrder::
        when($search, function ($query, $search) {
            return $query->where('order_number', $search);
        })
        ->where('order_status', 'pending')->orderBy('id', 'DESC')->paginate(10);
        return view('admin.product.order.index', $data);
    }

    public function processing(Request $request)
    {
        $search = $request->search;
        $data['orders'] = ProductOrder::where('order_status', 'processing')
        ->when($search, function ($query, $search) {
            return $query->where('order_number', $search);
        })
        ->orderBy('id', 'DESC')->paginate(10);
        return view('admin.product.order.index', $data);
    }

    public function completed(Request $request)
    {
        $search = $request->search;
        $data['orders'] = ProductOrder::where('order_status', 'completed')->
        when($search, function ($query, $search) {
            return $query->where('order_number', $search);
        })
        ->orderBy('id', 'DESC')->paginate(10);
        return view('admin.product.order.index', $data);
    }

    public function rejected(Request $request)
    {
        $search = $request->search;
        $data['orders'] = ProductOrder::where('order_status', 'rejected')->
        when($search, function ($query, $search) {
            return $query->where('order_number', $search);
        })
        ->orderBy('id', 'DESC')->paginate(10);
        return view('admin.product.order.index', $data);
    }

    public function status(Request $request)
    {

        $po = ProductOrder::find($request->order_id);
        // $po->order_status = $request->order_status;

        $order_status_all = $request->order_status_all[0];
        $order_status_data = explode('|', $order_status_all);
        $po->order_status = $order_status_data[0];
        $po->payment_status = $order_status_data[1];
        $po->save();
        

        $user = User::findOrFail($po->user_id);
        $be = BasicExtended::first();
        $sub = 'Order Status Update';

        $order_status_all = $request->order_status_all[0];
        $order_status_data = explode('|', $order_status_all);
        $po->order_status = $order_status_data[0];
        $to = $user->email;
         // Send Mail to Buyer
         $mail = new PHPMailer(true);
         if ($be->is_smtp == 1) {
             try {
                 $mail->isSMTP();
                 $mail->Host       = $be->smtp_host;
                 $mail->SMTPAuth   = true;
                 $mail->Username   = $be->smtp_username;
                 $mail->Password   = $be->smtp_password;
                 $mail->SMTPSecure = $be->encryption;
                 $mail->Port       = $be->smtp_port;

                 //Recipients
                 $mail->setFrom($be->from_mail, $be->from_name);
                 $mail->addAddress($user->email, $user->fname);

                

                 // Content
                 $mail->isHTML(true);
                 $mail->Subject = $sub;
                 $mail->Body    = 'Hello <strong>' . $user->fname . '</strong>,<br/>Your order status is '.$po->order_status.'.<br/>Thank you.';
                 $mail->send();
             } catch (Exception $e) {

             }
         } else {
             try {

                 //Recipients
                 $mail->setFrom($be->from_mail, $be->from_name);
                 $mail->addAddress($user->email, $user->fname);


                 // Content
                 $mail->isHTML(true);
                 $mail->Subject = $sub ;
                 $mail->Body    = 'Hello <strong>' . $user->fname . '</strong>,<br/>Your order status is '.$po->order_status.'.<br/>Thank you.';

                 $mail->send();
             } catch (Exception $e) {

             }
         }


        Session::flash('success', 'Order status changed successfully!');
        return back();
    }

    public function details($id)
    {
        $order = ProductOrder::findOrFail($id);
        return view('admin.product.order.details',compact('order'));
    }


    public function bulkOrderDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $order = ProductOrder::findOrFail($id);
            @unlink('assets/front/invoices/product/'.$order->invoice_number);
            foreach($order->orderitems as $item){
                $item->delete();
            }
            $order->delete();
        }

        Session::flash('success', 'Orders deleted successfully!');
        return "success";
    }

    public function orderDelete(Request $request)
    {
        $order = ProductOrder::findOrFail($request->order_id);
        @unlink('assets/front/invoices/product/'.$order->invoice_number);
        foreach($order->orderitems as $item){
            $item->delete();
        }
        $order->delete();

        Session::flash('success', 'product order deleted successfully!');
        return back();
    }

}
