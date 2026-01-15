<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Twilio\Rest\Client;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //

                TextInput::make('name')
                    ->required(),

                Select::make('office_space_id')
                    ->relationship('officeSpace', 'name')
                    ->preload()
                    ->required(),

                TextInput::make('phone_number')
                    ->required()
                    ->numeric(),

                TextInput::make('booking_trx_id')
                    ->required(),

                Select::make('is_paid')
                    ->required()
                    ->options([
                        true => 'Paid',
                        false => 'Not Paid',
                    ]),

                DatePicker::make('started_at')
                    ->required(),

                DatePicker::make('ended_at')
                    ->required(),

                TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->prefix('Days'),

                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('IDR'),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('officeSpace.name')
                    ->searchable(),

                TextColumn::make('booking_trx_id')
                    ->searchable(),
                

                TextColumn::make('phone_number'),

                TextColumn::make('started_at')
                    ->date(),

                TextColumn::make('total_amount'),

                IconColumn::make('is_paid')
                    ->boolean()

                    ->trueColor('success')
                    ->falseColor('danger')

                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Paid')

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (BookingTransaction $record) {
                        $record->is_paid = true;
                        $record->save();


                        Notification::make()
                            ->title('Booking Approved')
                            ->success()
                            ->body('The booking successfully approved')
                            ->send();

                        $sid = getenv("TWILIO_ACCOUNT_ID");
                        $token = getenv("TWILIO_AUTH_TOKEN");

                        $twilio = new Client($sid, $token);

                        $messageBody = "Hi, {$record->name}, Terima kasih telah mem-booking kantor di First Office.\n\n";
                        $messageBody .= "Pesanan kantor {$record->officeSpace->name} dengan Booking TRX ID: {$record->booking_trx_id}.\n\n";
                        $messageBody .= "Sudah di bayar, selamat menikmati !";


                        $message = $twilio->messages
                            ->create(
                                "whatsapp:+$record->phone_number", // to
                                array(
                                    "from" => "whatsapp:+14155238886",
                                    "body" => $messageBody
                                )
                            );
                    })
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
