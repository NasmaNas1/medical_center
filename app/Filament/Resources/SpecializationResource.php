<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecializationResource\Pages;
use App\Filament\Resources\SpecializationResource\RelationManagers;
use App\Models\Specialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecializationResource extends Resource
{
    protected static ?string $model = Specialization::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إضافة تخصص جديد')
                    ->icon('heroicon-s-plus-circle')
                    ->collapsible()
                    ->description('الرجاء إدخال بيانات التخصص')
                    ->schema([
                        TextInput::make('type')
                            ->label('نوع التخصص')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'حقل نوع التخصص مطلوب',
                                'max' => 'لا يمكن أن يتجاوز النوع 255 حرف'
                            ])
                            ->columnSpanFull(),
                    ])
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('type')
                ->label('نوع التخصص')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('created_at')
                ->label('تاريخ الإضافة')
                ->dateTime('d/M/Y')
        ])
        ->filters([
            // يمكن إضافة فلترات هنا
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->label('تعديل'),
            Tables\Actions\DeleteAction::make()
                ->label('حذف'),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('حذف المحدد'),
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
            'index' => Pages\ListSpecializations::route('/'),
            'create' => Pages\CreateSpecialization::route('/create'),
            'edit' => Pages\EditSpecialization::route('/{record}/edit'),
        ];
    }
}
