<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers;
use App\Models\Doctor;
use App\Models\Specialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Doctor';
    protected static ?string $pluralModelLabel = 'Doctors';
    protected static ?string $navigationLabel = 'القائمة الطبية';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إضافة طبيب جديد')
                    ->icon('heroicon-s-user-plus')
                    ->collapsible()
                    ->description('املأ جميع الحقول المطلوبة')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->columnSpan(1),
                            
                        Select::make('specialization_id')
                            ->label('الاختصاصات')
                            ->relationship('Specialization','type')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                            
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->columnSpan(1),
                            
                        FileUpload::make('image')
                            ->label('صورة الطبيب')
                            ->disk('public')
                            ->directory('doctors')
                            ->image()
                            ->maxSize(5000)
                            ->columnSpan(1),
                            
                        TextInput::make('practice')
                            ->label('سنوات الخبرة')
                            ->columnSpan(1),
                            
                        MarkdownEditor::make('about_doctor')
                            ->label('السيرة الذاتية')
                            ->required()
                            ->columnSpanFull()
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        //هون بحط الشي يلي لازم يظهر بعد اضافةة الطبيب يعني هوم العرض بالجداول
            ->columns([
                ImageColumn::make('image')
                ->disk('public') // تأكد من مطابقة القرص
                ->width(80)
                ->height(90)
                ->extraImgAttributes([
                    'class' => 'rounded-full object-cover', // Tailwind classes
                ]),
                TextColumn::make('name'),
                TextColumn::make('specialization.type')->label('Specializations'),
                TextColumn::make('email'),
                TextColumn::make('practice'),
                TextColumn::make('about_doctor')->label('About Doctor'),
               
            ])
            ->filters([
                //
            ])
            ->actions([
                
                Tables\Actions\EditAction::make()
                ->label('تعديل'),
                
                
            //   Tables\Actions\DeleteAction::make()
            //     ->label('حذف')
            //     ->requiresConfirmation(),
    

                Tables\Actions\Action::make('patients')
                ->label('المرضى')
                ->icon('heroicon-o-user-group') // أيقونة المرضى
                ->color('gray') // لون أخضر
                ->button() // عرض كزر بدلاً من الرابط
                ->url(fn ($record) => DoctorResource::getUrl('view', [
                   'record' => $record->id,
                   'activeRelationManager' => 'patients'
                  ])),
                
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
            RelationManagers\PatientsRelationManager::class,
        ];
       
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
            'view' => Pages\ViewDoctor::route('/{record}'),
        ];
    }
}
