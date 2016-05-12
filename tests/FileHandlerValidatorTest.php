<?php
namespace tecnocen\filehandler\tests;

use Yii;
use yii\web\UploadedFile;

/**
 * Test the functionality for the enum extension
 */
class FileHandlerValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Yii::setAlias(
            '@testFilePath',
            __DIR__ . '/data/files'
        );
        Yii::setAlias(
            '@testTmpPath',
            __DIR__ . '/data/tmp'
        );
    }

    public function testFile()
    {
        $model = new data\FileModel();
        $model->route = 'blank.pdf';
        $this->assertTrue($model->validate());
        $this->assertNull($model->getFirstError('route'));
    }
    public function testNotFoundFile()
    {
        $model = new data\FileModel();
        $model->route = 'notfound.pdf';
        $this->assertFalse($model->validate());
        $this->assertEquals(
            'The file "notfound.pdf" was not found on folder "@testFilePath/".',
            $model->getFirstError('route')
        );
    }

    public function testWrongExtension()
    {
        $model = new data\FileModel();
        $model->route = 'notimage.jpg';
        $model->validators[0]->extensions = ['pdf'];
        $this->assertFalse($model->validate());
        $this->assertEquals(
            'Only files with these extensions are allowed: pdf.',
            $model->getFirstError('route')
        );

        $model->route = 'notimage.jpg';
        $model->validators[0]->extensions = ['jpg'];
        $this->assertFalse($model->validate());
        $this->assertEquals(
            'Only files with these extensions are allowed: jpg.',
            $model->getFirstError('route')
        );
    }

    public function testEmpty()
    {
        $model = new data\FileModel();
        $emptyUpload = $this->createTestFiles([[
            'error' => UPLOAD_ERR_NO_FILE
        ]]);

        $emptyValues = [
            null,
            '',
            false,
            $emptyUpload,
        ];

        $model->validators[0]->skipOnEmpty = true;

        foreach ($emptyValues as $emptyValue) {
            $model->route = $emptyValue;
            $this->assertTrue($model->validate());
        }
    }

    public function testUploadedFile()
    {
        $model = new data\FileModel();
        $fileUpload = $this->createTestFiles([[
            'name' => 'upload.pdf',
        ]]);

        $model->route = $fileUpload;
        $this->assertFalse($model->validate());
        $this->assertEquals(
            'The file could not be saved to "@testFilePath/" error: "0".',
            $model->getFirstError('route')
        );
        $this->assertEquals('upload.pdf', $model->route);

        $model->route = $fileUpload;
        $model->validators[0]->fileName = function ($file, $model, $validator) {
            return 'generatedFileName.' . $file->extension;
        };

        $this->assertFalse($model->validate());
        $this->assertEquals('generatedFileName.pdf', $model->route);
    }

    /**
     * @param  array params
     * @return UploadedFile[]
     */
    protected function createTestFiles($params = [])
    {
        $files = [];
        foreach ($params as $param) {
            if (empty($param) && count($params) != 1) {
                $files[] = ['no instance of UploadedFile'];
                continue;
            }
            $name = isset($param['name'])
                ? $param['name']
                : Yii::$app->security->generateRandomString();
            $tempName = Yii::getAlias('@testTmpPath/') . $name;
            if (is_readable($tempName)) {
                $size = filesize($tempName);
            } else {
                $size = isset($param['size']) ? $param['size'] : rand(
                    1,
                    1024
                );
            }
            $type = isset($param['type']) ? $param['type'] : 'text/plain';
            $error = isset($param['error']) ? $param['error'] : UPLOAD_ERR_OK;
            if (count($params) == 1) {
                $error = empty($param) ? UPLOAD_ERR_NO_FILE : $error;
                return new UploadedFile([
                    'name' => $name,
                    'tempName' => $tempName,
                    'type' => $type,
                    'size' => $size,
                    'error' => $error
                ]);
            }
            $files[] = new UploadedFile([
                'name' => $name,
                'tempName' => $tempName,
                'type' => $type,
                'size' => $size,
                'error' => $error
            ]);
        }
        return $files;
    }
}
