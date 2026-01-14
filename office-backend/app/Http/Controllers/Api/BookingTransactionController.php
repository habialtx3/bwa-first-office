<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingDetailsRequest;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionResource;
use App\Models\BookingTransaction;
use App\Models\OfficeSpace;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class BookingTransactionController extends Controller
{
    //
    public function store(StoreBookingTransactionRequest $request)
    {
        $validatedData = $request->validated();

        $officeSpace = OfficeSpace::find($validatedData['office_space_id']);

        $validatedData['is_paid'] = false;
        $validatedData['booking_trx_id'] = BookingTransaction::generateUniqueTrxId();
        $validatedData['duration'] = $officeSpace->duration;

        $validatedData['ended_at'] = (new \DateTime($validatedData['started_at']))
            ->modify("+{$officeSpace->duration} days")->format('Y-m-d');
        $bookingTransaction = BookingTransaction::create($validatedData);

        $bookingTransaction->load('officeSpace');


        $sid = getenv('TWILIO_ACCOUNT_ID');
        $token = getenv('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        $messageBody = "Hi, {$bookingTransaction->name}, Terima kasih telah mem-booking kantor di First Office.\n\n";
        $messageBody .= "Pesanan kantor {$bookingTransaction->officeSpace->name} dengan Booking TRX ID: {$bookingTransaction->booking_trx_id}.\n\n";
        $messageBody .= "Kami akan menginformasikan kembali status pemesanan anda secepat mungkin";


        $message = $twilio->messages
            ->create(
                "+$bookingTransaction->phone_number", // to
                array(
                    "from" => getenv("TWILIO_PHONE_NUMBER"),
                    "body" => $messageBody
                )
            );

        return new BookingTransactionResource($bookingTransaction);
        //mengirim notif ke whatsapp
    }

    public function booking_details(BookingDetailsRequest $request)
    {
        $validatedData = $request->validated();

        $bookingDetails = BookingTransaction::where('booking_trx_id', $validatedData['booking_trx_id'])
            ->where('phone_number', $validatedData['phone_number'])->first();

        if (!$bookingDetails) {
            return response()->json(
                [
                    'message' => 'Booking not found'
                ],
                404
            );
        }

        $bookingDetails->load('officeSpace');
        return new BookingTransactionResource($bookingDetails);
    }
}
