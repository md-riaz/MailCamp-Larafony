<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\Seeders\CampaignSeeder;
use App\Database\Seeders\RbacSeeder;
use App\Database\Seeders\SmtpSettingSeeder;
use App\Database\Seeders\TemplateSeeder;
use App\Database\Seeders\UserSeeder;
use Larafony\Framework\Console\Attributes\AsCommand;
use Larafony\Framework\Console\Command;

#[AsCommand('app:seed')]
class SeedCommand extends Command
{
    public function run(): int
    {
        $this->output->info('Seeding demo data...');

        $this->output->writeln('✔ RbacSeeder');
        new RbacSeeder()->run();

        $this->output->writeln('✔ UserSeeder');
        new UserSeeder()->run();

        $this->output->writeln('✔ SmtpSettingSeeder');
        new SmtpSettingSeeder()->run();

        $this->output->writeln('✔ TemplateSeeder');
        new TemplateSeeder()->run();

        $this->output->writeln('✔ CampaignSeeder');
        new CampaignSeeder()->run();

        return 0;
    }
}
