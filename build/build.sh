#IGNORE="Model\DBObject System\Core\Router Library\Database\LinqEquality System\Model\DBObject";

for FILE in `grep @assert framework/ -r -l`; do
#for FILE in `find framework/ -type f -name "*php" | grep -v "/application/view/" | grep -v "/system/view/" | grep -v "/application/helper"`; do
#	echo $FILE
	CLASS=`echo $FILE | sed 's/\(framework\/\(application\/\)\?\)\(.*\).php/\3/' | sed 's/\/\([a-z]\)/\/\u\1/g' | sed 's/^\([a-z]\)/\u\1/' | sed 's/\//\\\\/g'`;
	TEST_FILE=`echo $FILE | sed 's/.php/Test.php/g'`;
#	echo $TEST_CLASS;
#	exit;
	DIR=`echo $FILE | sed 's/\(.*\/\).*/\1/g'`;

	mkdir -p tests/$DIR
#	echo $TEST_FILE
#SOME HACKS
	CLASS=`echo "$CLASS" | sed 's/System\\\\Library\\\\Database/Library\\\\Database/g'`
	TEST_CLASS=$CLASS"Test";
#	echo $CLASS
#	echo phpunit --bootstrap phpunit.php $CLASS $FILE "tests/$TEST_FILE";
#	phpunit --bootstrap phpunit.php $CLASS $FILE "tests/$TEST_FILE";

#	for I in $IGNORE; do
#		if [[ "$I" == "$CLASS" ]]; then
#			echo "IGNORING $I"
#			continue 2;
#		fi;
#	done;
	echo phpunit-skelgen --bootstrap build/phpunit.php --test -- "$CLASS" $FILE $TEST_CLASS "tests/$TEST_FILE";
	phpunit-skelgen --bootstrap build/phpunit.php --test -- "$CLASS" $FILE $TEST_CLASS "tests/$TEST_FILE";
#	exit;
done;

