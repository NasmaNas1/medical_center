<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubSpecializationResource\Pages;
use App\Models\SubSpecialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;

class SubSpecializationResource extends Resource
{   
    protected static ?string $model = SubSpecialization::class;
    
    protected static ?string $navigationGroup = 'Specialization';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
   protected static ?int $navigationSort = 4;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إضافة نوع حجز')
                    ->icon('heroicon-s-plus-circle')
                    ->description('يرجى تعبئة بيانات نوع الحجز بدقة')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم نوع الحجز')
                            ->required()
                            ->columnSpan(1),

                        Select::make('specialization_id')
                            ->label('التخصص المرتبط')
                            ->relationship('specialization', 'type')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('duration')
                            ->label('المدة بالدقائق')
                            ->numeric()
                            ->required()
                            ->suffix('دقيقة')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('اسم نوع الحجز'),
                TextColumn::make('specialization.type')->label('التخصص'),
                TextColumn::make('duration')->label('المدة')->suffix(' دقيقة'),
            ])
            ->filters([
                SelectFilter::make('specialization_id')
                    ->label('التخصص')
                    ->options(fn () => \App\Models\Specialization::pluck('type', 'id'))
                    ->searchable()
                    ->preload()
           
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubSpecializations::route('/'),
            'create' => Pages\CreateSubSpecialization::route('/create'),
            'edit' => Pages\EditSubSpecialization::route('/{record}/edit'),
        ];
    }
}
