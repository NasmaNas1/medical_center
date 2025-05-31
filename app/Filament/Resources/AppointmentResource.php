<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;



class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('doctor_id')
                ->label('الطبيب')
                ->relationship('doctor', 'name')
                ->required(),
                
            Select::make('patient_id')
                ->label('المريض')
                ->relationship('patient', 'name')
                ->required(),
                
            DateTimePicker::make('appointment_date')
                ->label('تاريخ ووقت الموعد')
                ->required(),
                
            Select::make('status')
                ->label('الحالة')
                ->options([
                    'pending' => 'قيد الانتظار',
                    'confirmed' => 'مؤكد',
                    'cancelled' => 'ملغي',
                ])
                ->default('pending'),
                
            Textarea::make('notes')
                ->label('ملاحظات')
                ->nullable(),
         
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('doctor.name')->label('الطبيب'),
            TextColumn::make('patient.name')->label('المريض'),
            TextColumn::make('appointment_date')->label('التاريخ'),
            TextColumn::make('status')->label('الحالة'),
            ])
            ->filters([
                            // 1. فلتر حسب الطبيب
            SelectFilter::make('doctor_id')
            ->label('الطبيب')
            ->relationship('doctor', 'name') 
            ->searchable() 
            ->preload(), 
          // 2. فلتر حسب المريض
          SelectFilter::make('patient_id')
            ->label('المريض')
            ->relationship('patient', 'name') 
            ->searchable()
            ->preload(),

          // 3. فلتر حسب الحالة
           SelectFilter::make('status')
            ->label('الحالة')
            ->options([
                'pending' => 'قيد الانتظار',
                'confirmed' => 'مؤكد',
                'cancelled' => 'ملغي',
            ]),

            TernaryFilter::make('status')
               ->label('الحالة')
               ->placeholder('الكل')
               ->trueLabel('مؤكد')
               ->falseLabel('ملغي')
              ->queries(
            true: fn (Builder $query) => $query->where('status', 'confirmed'),
            false: fn (Builder $query) => $query->where('status', 'cancelled'),
            blank: fn (Builder $query) => $query->where('status', 'pending'),
              ),
        Filter::make('appointment_date')
          ->label('تاريخ الموعد')
           ->form([
            DatePicker::make('start_date'),
            DatePicker::make('end_date'),
                 ])
         ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when(
                $data['start_date'],
                fn (Builder $query, $date) => $query->whereDate('appointment_date', '>=', $date)
            )
            ->when(
                $data['end_date'],
                fn (Builder $query, $date) => $query->whereDate('appointment_date', '<=', $date)
            );
        })
            ])
            ->actions([
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
